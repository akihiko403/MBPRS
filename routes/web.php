<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingCategoryController;
use App\Http\Controllers\BuildingPermitController;
use App\Http\Controllers\BuildingTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermitApprovalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::resource('building-permits', BuildingPermitController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/building-permits/{buildingPermit}/documents/{document}/preview', [BuildingPermitController::class, 'previewDocument'])->name('building-permits.documents.preview');
    Route::get('/building-permits/{buildingPermit}/documents/{document}', [BuildingPermitController::class, 'downloadDocument'])->name('building-permits.documents.download');
    Route::resource('building-types', BuildingTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('building-categories', BuildingCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/permit-approvals', [PermitApprovalController::class, 'index'])->name('permit-approvals.index');
    Route::patch('/permit-approvals/{buildingPermit}/status', [PermitApprovalController::class, 'updateStatus'])->name('permit-approvals.update-status');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggle'])->name('users.toggle');
    Route::patch('/users/{user}/reset-password', [UserManagementController::class, 'reset-password'])->name('users.reset-password');
});


