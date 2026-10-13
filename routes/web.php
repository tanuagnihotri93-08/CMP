<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/login', [AuthController::class, 'authenticate'])
    ->middleware('throttle:5,1');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect('/login');

});

/*
|--------------------------------------------------------------------------
| Protected CRM Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->whereUuid('notification')
        ->name('notifications.read');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard')
        ->name('dashboard');

    Route::resource('leads', LeadController::class)
        ->middleware('permission:leads');

    Route::resource('customers', CustomerController::class)
        ->middleware('permission:customers');

    Route::post('/followups/{followUp}/complete', [FollowUpController::class, 'complete'])
        ->middleware('permission:follow_ups')
        ->name('followups.complete');

    Route::resource('followups', FollowUpController::class)
        ->middleware('permission:follow_ups');

    Route::resource('activities', ActivityController::class)
        ->middleware('permission:activities');

    /*
    |--------------------------------------------------------------------------
    | Project Management
    |--------------------------------------------------------------------------
    */

    Route::resource('projects', ProjectController::class);

    /*
    |--------------------------------------------------------------------------
    | Task Management
    |--------------------------------------------------------------------------
    */

    Route::resource('tasks', TaskController::class);

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:audit_logs')
        ->name('audit-logs.index');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports')
        ->name('reports.index');

    Route::get('/reports/{module}/export/{format}', [ReportController::class, 'export'])
        ->middleware('permission:reports.export')
        ->name('reports.export');

    Route::prefix('admin')->name('admin.')->group(function (): void {

        Route::middleware('permission:users')->group(function (): void {

            Route::get('/users', [UserManagementController::class, 'index'])
                ->name('users.index');

            Route::put('/users/{user}', [UserManagementController::class, 'update'])
                ->name('users.update');

        });

        Route::middleware('permission:roles')->group(function (): void {

            Route::get('/roles', [RoleManagementController::class, 'index'])
                ->name('roles.index');

            Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])
                ->name('roles.edit');

            Route::put('/roles/{role}', [RoleManagementController::class, 'update'])
                ->name('roles.update');

        });

    });

    Route::get('/performance', [PerformanceController::class, 'index'])
        ->name('performance.index');

});
