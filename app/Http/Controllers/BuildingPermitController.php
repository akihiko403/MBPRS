<?php

namespace App\Http\Controllers;

use App\Models\BuildingCategory;
use App\Models\BuildingPermit;
use App\Models\BuildingPermitDocument;
use App\Models\BuildingType;
use App\Models\PermitStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuildingPermitController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        return view('building-permits.index', [
            'title' => 'Building Permit',
            'subtitle' => 'Manage building permit records, attached documents, and permit status updates.',
            'permits' => BuildingPermit::query()
                ->with(['buildingType', 'buildingCategory', 'creator', 'approver'])
                ->filter($request->only(['search', 'status', 'building_type_id', 'building_category_id']))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'buildingTypes' => BuildingType::query()->orderBy('name')->get(),
            'buildingCategories' => BuildingCategory::query()->orderBy('name')->get(),
            'statuses' => BuildingPermit::statuses(),
            'nextPermitId' => BuildingPermit::generatePermitId(),
            'selectedPermit' => $request->query('show') ? BuildingPermit::query()->with(['buildingType', 'buildingCategory', 'statusLogs.actor', 'documents'])->find($request->query('show')) : null,
            'editPermit' => $request->query('edit') ? BuildingPermit::query()->with('documents')->find($request->query('edit')) : null,
            'deletedPermits' => BuildingPermit::query()->onlyTrashed()->with(['buildingType', 'buildingCategory'])->latest('deleted_at')->get(),
            'readOnly' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        $validated = $this->validatedPermit($request);
        $validated['permit_id'] = BuildingPermit::generatePermitId();
        $validated['status'] = BuildingPermit::STATUS_PENDING;
        $validated['created_by'] = $request->user()->id;

        $permit = BuildingPermit::query()->create($validated);
        $this->storeUploadedDocuments($request, $permit);

        PermitStatusLog::query()->create([
            'building_permit_id' => $permit->id,
            'old_status' => null,
            'new_status' => $permit->status,
            'remarks' => 'Permit created.',
            'acted_by' => $request->user()->id,
        ]);

        return redirect()->route('building-permits.index')->with('success', 'Building permit saved with Pending status.');
    }

    public function show(BuildingPermit $buildingPermit): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        $buildingPermit->load(['buildingType', 'buildingCategory', 'creator', 'approver', 'statusLogs.actor', 'documents']);

        return view('building-permits.show', ['permit' => $buildingPermit]);
    }

    public function update(Request $request, BuildingPermit $buildingPermit): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        $buildingPermit->update($this->validatedPermit($request, $buildingPermit));
        $this->storeUploadedDocuments($request, $buildingPermit);

        return redirect()->route('building-permits.index')->with('success', 'Building permit updated successfully.');
    }

    public function destroy(Request $request, BuildingPermit $buildingPermit): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can delete records.');
        }

        $buildingPermit->delete();

        return redirect()->route('building-permits.index')->with('success', 'Building permit moved to trash.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can restore records.');
        }

        $permit = BuildingPermit::query()->onlyTrashed()->findOrFail($id);
        $permit->restore();

        return back()->with('success', 'Building permit restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can permanently delete records.');
        }

        $permit = BuildingPermit::query()->onlyTrashed()->findOrFail($id);
        $permit->forceDelete();

        return back()->with('success', 'Building permit permanently deleted.');
    }

    public function previewDocument(Request $request, BuildingPermit $buildingPermit, BuildingPermitDocument $document): BinaryFileResponse|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        abort_unless($document->building_permit_id === $buildingPermit->id, 404);

        $path = Storage::disk('local')->path($document->path);
        $headers = [
            'Content-Type' => $document->mime_type ?: mime_content_type($path),
            'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
        ];

        return response()->file($path, $headers);
    }

    public function downloadDocument(Request $request, BuildingPermit $buildingPermit, BuildingPermitDocument $document): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-permits')) {
            return $redirect;
        }

        abort_unless($document->building_permit_id === $buildingPermit->id, 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    private function validatedPermit(Request $request, ?BuildingPermit $buildingPermit = null): array
    {
        return $request->validate([
            'owner_last_name' => ['required', 'string', 'max:255'],
            'owner_first_name' => ['required', 'string', 'max:255'],
            'owner_middle_name' => ['nullable', 'string', 'max:255'],
            'owner_suffix' => ['nullable', 'string', 'max:255'],
            'building_type_id' => ['required', 'exists:building_types,id'],
            'building_category_id' => ['required', 'exists:building_categories,id'],
            'barangay' => ['required', 'string', 'max:255'],
            'city_municipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'documents' => [
                'nullable',
                'array',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) use ($buildingPermit) {
                    $existingCount = $buildingPermit?->documents()->count() ?? 0;
                    $incomingCount = is_array($value) ? count($value) : 0;

                    if (($existingCount + $incomingCount) > 20) {
                        $fail('A permit can only have up to 20 documents.');
                    }
                },
            ],
            'documents.*' => ['file', 'max:10240'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function storeUploadedDocuments(Request $request, BuildingPermit $permit): void
    {
        /** @var UploadedFile[] $documents */
        $documents = $request->file('documents', []);

        foreach ($documents as $document) {
            $storedName = Str::uuid()->toString().'.'.$document->getClientOriginalExtension();
            $path = $document->storeAs('permit-documents/'.$permit->id, $storedName, 'local');

            $permit->documents()->create([
                'original_name' => $document->getClientOriginalName(),
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $document->getClientMimeType(),
                'size' => $document->getSize(),
            ]);
        }
    }
}
