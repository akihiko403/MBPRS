<?php

namespace App\Http\Controllers;

use App\Models\BuildingPermit;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('dashboard')) {
            return $redirect;
        }

        $permits = BuildingPermit::query()
            ->with(['buildingType', 'buildingCategory'])
            ->latest()
            ->get();

        $statusCounts = collect(BuildingPermit::statuses())
            ->mapWithKeys(fn (string $status) => [$status => $permits->where('status', $status)->count()]);

        $statusTotal = max($statusCounts->sum(), 1);
        $statusColors = [
            BuildingPermit::STATUS_PENDING => '#f59e0b',
            BuildingPermit::STATUS_APPROVED => '#15803d',
            BuildingPermit::STATUS_REJECTED => '#b91c1c',
            BuildingPermit::STATUS_RETURNED => '#c2410c',
        ];
        $currentPercent = 0;
        $donutSegments = $statusCounts
            ->map(function (int $count, string $status) use ($statusTotal, $statusColors, &$currentPercent): string {
                $nextPercent = $currentPercent + (($count / $statusTotal) * 100);
                $segment = ($statusColors[$status] ?? '#64748b').' '.$currentPercent.'% '.$nextPercent.'%';
                $currentPercent = $nextPercent;

                return $segment;
            })
            ->implode(', ');

        $monthlyPeriod = CarbonPeriod::create(now()->subMonths(5)->startOfMonth(), '1 month', now()->startOfMonth());
        $monthlyCounts = collect($monthlyPeriod)
            ->map(function ($month) use ($permits): array {
                return [
                    'label' => $month->format('M'),
                    'count' => $permits->filter(fn (BuildingPermit $permit) => $permit->created_at?->isSameMonth($month))->count(),
                ];
            })
            ->values();
        $monthlyMax = max($monthlyCounts->max('count') ?: 0, 1);

        $categoryBreakdown = $permits
            ->groupBy(fn (BuildingPermit $permit) => $permit->buildingCategory?->name ?: 'Uncategorized')
            ->map(fn ($items, string $label) => ['label' => $label, 'count' => $items->count()])
            ->sortByDesc('count')
            ->take(5)
            ->values();
        $categoryMax = max($categoryBreakdown->max('count') ?: 0, 1);

        $typeBreakdown = $permits
            ->groupBy(fn (BuildingPermit $permit) => $permit->buildingType?->name ?: 'Unspecified')
            ->map(fn ($items, string $label) => ['label' => $label, 'count' => $items->count()])
            ->sortByDesc('count')
            ->take(5)
            ->values();
        $typeMax = max($typeBreakdown->max('count') ?: 0, 1);

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'subtitle' => 'Permit overview, recent activity, and quick access to core modules.',
            'stats' => [
                'total' => $permits->count(),
                'pending' => $statusCounts[BuildingPermit::STATUS_PENDING] ?? 0,
                'approved' => $statusCounts[BuildingPermit::STATUS_APPROVED] ?? 0,
                'returned_or_rejected' => ($statusCounts[BuildingPermit::STATUS_REJECTED] ?? 0) + ($statusCounts[BuildingPermit::STATUS_RETURNED] ?? 0),
            ],
            'analytics' => [
                'statusCounts' => $statusCounts,
                'statusColors' => $statusColors,
                'donutSegments' => $donutSegments ?: '#d7e1dd 0% 100%',
                'monthlyCounts' => $monthlyCounts,
                'monthlyMax' => $monthlyMax,
                'categoryBreakdown' => $categoryBreakdown,
                'categoryMax' => $categoryMax,
                'typeBreakdown' => $typeBreakdown,
                'typeMax' => $typeMax,
            ],
            'recentPermits' => BuildingPermit::query()
                ->with(['buildingType', 'buildingCategory'])
                ->filter($request->only('search'))
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
