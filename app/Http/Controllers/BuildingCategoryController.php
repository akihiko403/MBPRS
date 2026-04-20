<?php

namespace App\Http\Controllers;

use App\Models\BuildingCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuildingCategoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        return view('building-categories.index', [
            'title' => 'Building Category',
            'subtitle' => 'Manage building categories used to classify permit applications and records.',
            'items' => BuildingCategory::query()
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
            'selectedItem' => $request->query('show') ? BuildingCategory::query()->find($request->query('show')) : null,
            'editItem' => $request->query('edit') ? BuildingCategory::query()->find($request->query('edit')) : null,
            'deletedItems' => BuildingCategory::query()->onlyTrashed()->latest('deleted_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:building_categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        BuildingCategory::query()->create($validated + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Building category added successfully.');
    }

    public function update(Request $request, BuildingCategory $buildingCategory): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('building_categories', 'name')->ignore($buildingCategory->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $buildingCategory->update($validated);

        return redirect()->route('building-categories.index')->with('success', 'Building category updated successfully.');
    }

    public function destroy(BuildingCategory $buildingCategory): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        if (! auth()->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can delete records.');
        }

        if ($buildingCategory->permits()->exists()) {
            return back()->with('error', 'Building category cannot be deleted because it is already used by permit records.');
        }

        $buildingCategory->delete();

        return back()->with('success', 'Building category moved to trash.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can restore records.');
        }

        BuildingCategory::query()->onlyTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Building category restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('building-categories')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can permanently delete records.');
        }

        $buildingCategory = BuildingCategory::query()->onlyTrashed()->findOrFail($id);

        if ($buildingCategory->permits()->exists()) {
            return back()->with('error', 'Building category cannot be permanently deleted because it is already used by permit records.');
        }

        $buildingCategory->forceDelete();

        return back()->with('success', 'Building category permanently deleted.');
    }
}
