<?php

namespace App\Http\Controllers;

use App\Models\BuildingPermit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('dashboard')) {
            return $redirect;
        }

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'subtitle' => 'Permit overview, recent activity, and quick access to core modules.',
            'stats' => [
                'total' => BuildingPermit::query()->count(),
                'pending' => BuildingPermit::query()->where('status', BuildingPermit::STATUS_PENDING)->count(),
                'approved' => BuildingPermit::query()->where('status', BuildingPermit::STATUS_APPROVED)->count(),
                'returned_or_rejected' => BuildingPermit::query()->whereIn('status', [BuildingPermit::STATUS_REJECTED, BuildingPermit::STATUS_RETURNED])->count(),
            ],
            'recentPermits' => BuildingPermit::query()->with(['buildingType', 'buildingCategory'])->latest()->take(8)->get(),
        ]);
    }
}
