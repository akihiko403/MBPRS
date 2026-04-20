<?php

namespace App\Http\Controllers;

use App\Models\BuildingCategory;
use App\Models\BuildingPermit;
use App\Models\BuildingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('reports')) {
            return $redirect;
        }

        $reportType = $request->input('report_type', 'approved');
        $filters = $request->only(['month', 'year', 'barangay', 'city_municipality', 'province', 'status', 'date_from', 'date_to', 'building_type_id', 'building_category_id']);

        return view('reports.index', [
            'title' => 'Reports',
            'subtitle' => 'Generate filtered permit reports and export records for monitoring and filing.',
            'records' => $this->reportQuery($reportType, $filters)
                ->with(['buildingType', 'buildingCategory', 'approver'])
                ->latest()
                ->get(),
            'reportType' => $reportType,
            'filters' => $filters,
            'buildingTypes' => BuildingType::query()->orderBy('name')->get(),
            'buildingCategories' => BuildingCategory::query()->orderBy('name')->get(),
            'reportTypes' => $this->reportTypes(),
        ]);
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('reports')) {
            return $redirect;
        }

        $reportType = $request->input('report_type', 'approved');
        $filters = $request->only(['month', 'year', 'barangay', 'city_municipality', 'province', 'status', 'date_from', 'date_to', 'building_type_id', 'building_category_id']);
        $records = $this->reportQuery($reportType, $filters)
            ->with(['buildingType', 'buildingCategory'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Permit ID', 'Owner', 'Building Type', 'Building Category', 'Barangay', 'City/Municipality', 'Province', 'Status', 'Created At']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->permit_id,
                    $record->owner_full_name,
                    $record->buildingType?->name,
                    $record->buildingCategory?->name,
                    $record->barangay,
                    $record->city_municipality,
                    $record->province,
                    $record->status,
                    $record->created_at?->format('M d, Y'),
                ]);
            }

            fclose($handle);
        }, 'report-'.$reportType.'-'.now()->format('YmdHis').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function reportQuery(string $reportType, array $filters)
    {
        $query = BuildingPermit::query()->filter($filters);

        return match ($reportType) {
            'approved' => $query->where('status', BuildingPermit::STATUS_APPROVED),
            'pending' => $query->where('status', BuildingPermit::STATUS_PENDING),
            'month_year' => $query,
            'barangay' => $query,
            default => $query,
        };
    }

    private function reportTypes(): array
    {
        return [
            'approved' => 'List of approved permits',
            'pending' => 'Pending permit applications',
            'month_year' => 'Permits by month/year',
            'barangay' => 'Permits by barangay',
        ];
    }
}
