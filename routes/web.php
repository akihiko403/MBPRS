<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupRestoreController;
use App\Http\Controllers\BuildingCategoryController;
use App\Http\Controllers\BuildingPermitController;
use App\Http\Controllers\BuildingTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermitApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/reset-password', [ProfileController::class, 'resetPassword'])->name('profile.reset-password');
    Route::get('/settings', SettingsController::class)->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/backup-restore', [BackupRestoreController::class, 'index'])->name('backup-restore.index');
    Route::post('/backup-restore/backup', [BackupRestoreController::class, 'backup'])->name('backup-restore.backup');
    Route::post('/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('backup-restore.restore');
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::patch('/building-permits/trash/{id}/restore', [BuildingPermitController::class, 'restore'])->name('building-permits.restore');
    Route::delete('/building-permits/trash/{id}/force-delete', [BuildingPermitController::class, 'forceDelete'])->name('building-permits.force-delete');
    Route::resource('building-permits', BuildingPermitController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/building-permits/{buildingPermit}/documents/{document}/preview', [BuildingPermitController::class, 'previewDocument'])->name('building-permits.documents.preview');
    Route::get('/building-permits/{buildingPermit}/documents/{document}', [BuildingPermitController::class, 'downloadDocument'])->name('building-permits.documents.download');
    Route::patch('/building-types/trash/{id}/restore', [BuildingTypeController::class, 'restore'])->name('building-types.restore');
    Route::delete('/building-types/trash/{id}/force-delete', [BuildingTypeController::class, 'forceDelete'])->name('building-types.force-delete');
    Route::resource('building-types', BuildingTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/building-categories/trash/{id}/restore', [BuildingCategoryController::class, 'restore'])->name('building-categories.restore');
    Route::delete('/building-categories/trash/{id}/force-delete', [BuildingCategoryController::class, 'forceDelete'])->name('building-categories.force-delete');
    Route::resource('building-categories', BuildingCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/permit-approvals', [PermitApprovalController::class, 'index'])->name('permit-approvals.index');
    Route::patch('/permit-approvals/{buildingPermit}/status', [PermitApprovalController::class, 'updateStatus'])->name('permit-approvals.update-status');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/trash/{id}/restore', [UserManagementController::class, 'restore'])->name('users.restore');
    Route::delete('/users/trash/{id}/force-delete', [UserManagementController::class, 'forceDelete'])->name('users.force-delete');
    Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggle'])->name('users.toggle');
    Route::patch('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
});


