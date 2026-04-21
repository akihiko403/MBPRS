<?php

namespace App\Http\Controllers;

use App\Models\BuildingCategory;
use App\Models\BuildingPermit;
use App\Models\BuildingType;
use App\Models\SystemSetting;
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
        $filters = $request->only(['search', 'month', 'year', 'barangay', 'city_municipality', 'province', 'status', 'date_from', 'date_to', 'building_type_id', 'building_category_id']);

        return view('reports.index', [
            'title' => 'Reports',
            'subtitle' => 'Generate filtered permit reports and export records for monitoring and filing.',
            'records' => $this->reportRecords($reportType, $filters),
            'reportType' => $reportType,
            'filters' => $filters,
            'buildingTypes' => BuildingType::query()->orderBy('name')->get(),
            'buildingCategories' => BuildingCategory::query()->orderBy('name')->get(),
            'reportTypes' => $this->reportTypes(),
        ]);
    }

    public function print(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('reports')) {
            return $redirect;
        }

        $reportType = $request->input('report_type', 'approved');
        $filters = $request->only(['search', 'month', 'year', 'barangay', 'city_municipality', 'province', 'status', 'date_from', 'date_to', 'building_type_id', 'building_category_id']);
        $systemSettings = SystemSetting::current();

        return view('reports.print', [
            'records' => $this->reportRecords($reportType, $filters),
            'reportHeading' => 'Municipal Building Permits',
            'reportSubheading' => $systemSettings->system_subheader ?? 'Municipality of Lebak',
        ]);
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('reports')) {
            return $redirect;
        }

        $reportType = $request->input('report_type', 'approved');
        $filters = $request->only(['search', 'month', 'year', 'barangay', 'city_municipality', 'province', 'status', 'date_from', 'date_to', 'building_type_id', 'building_category_id']);
        $records = $this->reportRecords($reportType, $filters);
        $systemSettings = SystemSetting::current();

        return response()->streamDownload(function () use ($records, $systemSettings): void {
            echo view('reports.export-excel', [
                'records' => $records,
                'reportHeading' => 'Municipal Building Permits',
                'reportSubheading' => $systemSettings->system_subheader ?? 'Municipality of Lebak',
            ])->render();
        }, 'report-'.$reportType.'-'.now()->format('YmdHis').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function reportRecords(string $reportType, array $filters)
    {
        return $this->reportQuery($reportType, $filters)
            ->with(['buildingType', 'buildingCategory', 'approver'])
            ->latest()
            ->get();
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
