<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('audit-logs')) {
            return $redirect;
        }

        $filters = $request->only('search');

        return view('audit-logs.index', [
            'title' => 'Audit Log',
            'subtitle' => 'Track user login time, logout time, and system activity.',
            'logs' => AuditLog::query()
                ->with('user')
                ->filter($filters)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
