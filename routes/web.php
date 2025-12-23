<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PatrolController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\DB;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/* Attendance */
Route::prefix('attendance')->group(function () {
    Route::get('/summary', [AttendanceController::class, 'summary'])->name('attendance.summary');
    Route::get('/explorer', [AttendanceController::class, 'explorer'])->name('attendance.explorer');
});

/* Patrol */
Route::prefix('patrol')->group(function () {
    Route::get('/foot-summary', [PatrolController::class, 'footSummary'])->name('patrol.foot.summary');
    Route::get('/foot-explorer', [PatrolController::class, 'footExplorer'])->name('patrol.foot.explorer');
    Route::get('/night-summary', [PatrolController::class, 'nightSummary'])->name('patrol.night.summary');
    Route::get('/night-explorer', [PatrolController::class, 'nightExplorer'])->name('patrol.night.explorer');
    Route::get('/maps', [PatrolController::class, 'maps'])->name('patrol.maps');
});

/* Reports */
Route::prefix('reports')->group(function () {
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/camera-tracking', [ReportController::class, 'cameraTracking'])->name('reports.camera');
Route::get('/reports/foot-report/export/pdf', function () {
    return "PDF Export Coming Soon";
})->name('export.foot.pdf');

Route::get('/reports/foot-report/export/excel', function () {
    return "Excel Export Coming Soon";
})->name('export.foot.excel');
    Route::get('/night-report', [ReportController::class, 'nightReport'])->name('reports.night');
Route::get('/attendance/export/excel',[AttendanceController::class,'exportExcel']);
Route::get('/attendance/export/pdf',[AttendanceController::class,'exportPdf']);

});

Route::get('/filters/beats', function () {
    return DB::table('site_details')
        ->where('client_name', request('range'))
        ->select('name')
        ->distinct()
        ->orderBy('name')
        ->get();
});

Route::get('/filters/geofences', function () {
    return DB::table('attendance')
        ->where('geo_name', '!=', '')
        ->where('site_name', request('beat'))
        ->select('geo_name')
        ->distinct()
        ->orderBy('geo_name')
        ->get();
});

