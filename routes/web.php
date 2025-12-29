<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PatrolController;
use App\Http\Controllers\PatrolAnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\ExecutiveAnalyticsController;
use App\Http\Controllers\GuardDetailController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/* API Routes */
Route::prefix('api')->group(function () {
    Route::get('/guard-details/{guardId}', [GuardDetailController::class, 'getGuardDetails']);
    Route::get('/patrol-session/{sessionId}', [PatrolController::class, 'getSessionDetails']);
});

/* Executive Analytics */
Route::get('/analytics/executive', [ExecutiveAnalyticsController::class, 'executiveDashboard'])->name('analytics.executive');

/* Attendance */
Route::prefix('attendance')->group(function () {
    Route::get('/summary', [AttendanceController::class, 'summary']);
    Route::get('/explorer', [AttendanceController::class, 'explorer']);
});


/* Patrol */
Route::prefix('patrol')->group(function () {
    Route::get('/foot-summary', [PatrolController::class, 'footSummary'])->name('patrol.foot.summary');
    Route::get('/night-summary', [PatrolController::class, 'nightSummary'])->name('patrol.night.summary');
    Route::get('/night-explorer', [PatrolController::class, 'nightExplorer'])->name('patrol.night.explorer');
    Route::get('/patrol/analytics', [PatrolAnalyticsController::class, 'patrolAnalytics'])
    ->name('patrol.analytics');
    Route::get('/foot-explorer', [PatrolController::class, 'footExplorer'])->name('patrol.foot.explorer');
    Route::get('/patrol/foot/guard-distance',[PatrolController::class,'footDistanceByGuard'])->name('patrol.foot.guard.distance');

    // Route::get('/maps', [PatrolController::class, 'maps'])->name('patrol.maps');
    Route::get('/maps', [PatrolController::class, 'kmlView'])->name('patrol.kml.view');
Route::get('/guard-details/{id}', [PatrolController::class, 'guardDetailsApi']);
});

/* Reports */
Route::prefix('reports')->group(function () {
    Route::get('/monthly', [ReportController::class, 'monthly']);
    Route::get('/camera-tracking', [ReportController::class, 'cameraTracking']);
     Route::get('/foot-report',  [ReportController::class, 'footReport'])->name('reports.foot');
    Route::get('/night-report', [ReportController::class, 'nightReport'])->name('reports.night');

});


Route::get('/filters/beats/{range}', [FilterController::class, 'beats']);
Route::get('/filters/compartments/{beat}', [FilterController::class, 'compartments']);


    Route::prefix('incidents')->group(function () {

    Route::get('/summary', [IncidentController::class, 'summary']);

    Route::get('/explorer', [IncidentController::class, 'explorer']);

});

Route::get('/guard-details/{id}', [PatrolController::class, 'guardDetails']);


