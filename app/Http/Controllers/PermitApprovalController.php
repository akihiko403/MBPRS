<?php

namespace App\Http\Controllers;

use App\Models\BuildingPermit;
use App\Models\PermitStatusLog;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitApprovalController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfMissingRole(Role::ADMIN, Role::ADMINISTRATOR)) {
            return $redirect;
        }

        return view('permit-approvals.index', [
            'title' => 'Permit Approval',
            'subtitle' => 'Review pending permit applications and update their approval status.',
            'pendingPermits' => BuildingPermit::query()
                ->with(['buildingType', 'buildingCategory'])
                ->filter($request->only('search'))
                ->where('status', BuildingPermit::STATUS_PENDING)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function updateStatus(Request $request, BuildingPermit $buildingPermit): RedirectResponse
    {
        if ($redirect = $this->redirectIfMissingRole(Role::ADMIN, Role::ADMINISTRATOR)) {
            return $redirect;
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected,Returned'],
            'remarks' => ['nullable', 'string'],
        ]);

        $oldStatus = $buildingPermit->status;
        $newStatus = $validated['status'];

        $buildingPermit->update([
            'status' => $newStatus,
            'remarks' => $validated['remarks'] ?? $buildingPermit->remarks,
            'approved_by' => $request->user()->id,
            'approved_at' => $newStatus === BuildingPermit::STATUS_APPROVED ? now() : null,
        ]);

        PermitStatusLog::query()->create([
            'building_permit_id' => $buildingPermit->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks' => $validated['remarks'] ?? null,
            'acted_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Permit status updated successfully.');
    }
}
