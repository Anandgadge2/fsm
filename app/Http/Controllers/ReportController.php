<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FootDefaulterExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FilterDataTrait;
class ReportController extends Controller
{
     use FilterDataTrait;
    public function monthly()
    {
        $monthly = DB::table('patrol_sessions')
            ->selectRaw('MONTH(started_at) as month, SUM(distance) as total_distance')
            ->groupByRaw('MONTH(started_at)')
            ->orderBy('month')
            ->get();

        return view('reports.monthly', array_merge(
            $this->filterData(),
            compact('monthly')
        ));
    }

    public function cameraTracking()
    {
        $logs = DB::table('patrol_logs')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('reports.camera-tracking', array_merge(
            $this->filterData(),
            compact('logs')
        ));
    }

    public function footReport()
{
    $defaulters = DB::table('users')
        ->leftJoin('patrol_sessions', function ($join) {
            $join->on('users.id','=','patrol_sessions.user_id')
                 ->where('patrol_sessions.session','Foot');
        })
        ->select(
            'users.name',
            DB::raw('COUNT(patrol_sessions.id) as patrols'),
            DB::raw('SUM(patrol_sessions.distance) as distance')
        )
        ->groupBy('users.id','users.name')
        ->havingRaw('patrols < 5 OR distance < 10')
        ->get();

    return view('reports.foot-report', compact('defaulters'));
}


    public function nightReport()
    {
        return view('reports.night-report', $this->filterData());
    }

public function exportFootPdf()
{
    $data = DB::table('users')
        ->leftJoin('patrol_sessions', function ($join) {
            $join->on('users.id','=','patrol_sessions.user_id')
                 ->where('patrol_sessions.session','Foot');
        })
        ->select(
            'users.name',
            DB::raw('COUNT(patrol_sessions.id) as patrols'),
            DB::raw('SUM(patrol_sessions.distance) as distance')
        )
        ->groupBy('users.id','users.name')
        ->havingRaw('patrols < 5 OR distance < 10')
        ->get();
    return Pdf::loadView('exports.foot', compact('data'))->download('foot_defaulters.pdf');
}



}
