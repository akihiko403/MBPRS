<?php

namespace App\Http\Controllers;

use App\Models\BuildingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuildingTypeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        return view('building-types.index', [
            'title' => 'Building Type',
            'subtitle' => 'Manage building types available for permit encoding and record classification.',
            'items' => BuildingType::query()
                ->when($request->input('search'), function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'selectedItem' => $request->query('show') ? BuildingType::query()->find($request->query('show')) : null,
            'editItem' => $request->query('edit') ? BuildingType::query()->find($request->query('edit')) : null,
            'deletedItems' => BuildingType::query()->onlyTrashed()->latest('deleted_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:building_types,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        BuildingType::query()->create($validated + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Building type added successfully.');
    }

    public function update(Request $request, BuildingType $buildingType): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('building_types', 'name')->ignore($buildingType->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $buildingType->update($validated);

        return redirect()->route('building-types.index')->with('success', 'Building type updated successfully.');
    }

    public function destroy(BuildingType $buildingType): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        if (! auth()->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can delete records.');
        }

        if ($buildingType->permits()->exists()) {
            return back()->with('error', 'Building type cannot be deleted because it is already used by permit records.');
        }

        $buildingType->delete();

        return back()->with('success', 'Building type moved to trash.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can restore records.');
        }

        BuildingType::query()->onlyTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Building type restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-types')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can permanently delete records.');
        }

        $buildingType = BuildingType::query()->onlyTrashed()->findOrFail($id);

        if ($buildingType->permits()->exists()) {
            return back()->with('error', 'Building type cannot be permanently deleted because it is already used by permit records.');
        }

        $buildingType->forceDelete();

        return back()->with('success', 'Building type permanently deleted.');
    }
}
