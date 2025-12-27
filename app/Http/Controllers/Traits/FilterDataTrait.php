<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\DB;

trait FilterDataTrait
{
    /**
     * Strict dependent global filters
     * Range → Beat → Compartment
     */
    public function filterData(): array
    {
        /* ===============================
           RANGE (client_details ONLY)
        =============================== */
        $ranges = DB::table('client_details')
            ->where('isActive', 1)
            ->orderBy('name')
            ->pluck('name', 'id'); // id => name

        /* ===============================
           BEAT (site_details via client_id)
        =============================== */
        $beats = collect();

        if (request()->filled('range')) {
            $beats = DB::table('site_details')
                ->where('client_id', request('range'))
                ->orderBy('name')
                ->pluck('name', 'id'); // id => name
        }

        /* ===============================
           COMPARTMENT (site_geofences via site_id)
        =============================== */
        $compartments = collect();

        if (request()->filled('beat')) {
            $compartments = DB::table('site_geofences')
                ->where('site_id', request('beat'))
                ->orderBy('name')
                ->pluck('name', 'id'); // id => name
        }

        return compact('ranges', 'beats', 'compartments');
    }

    /**
     * Apply filters to any query
     */
//     protected function applyFilters($query)
// {
//     if (request()->filled('start_date')) {
//         $query->whereDate('patrol_sessions.started_at', '>=', request('start_date'));
//     }

//     if (request()->filled('end_date')) {
//         $query->whereDate('patrol_sessions.started_at', '<=', request('end_date'));
//     }

//     if (request()->filled('range')) {
//         $query->where('site_details.client_id', request('range'));
//     }

//     if (request()->filled('beat')) {
//         $query->where('patrol_sessions.site_id', request('beat'));
//     }

//     if (request()->filled('compartment')) {
//         $query->where('site_geofences.id', request('compartment'));
//     }

//     return $query;
// }

protected function applyGlobalFilters($query, array $map)
{
    if (request()->filled('start_date') && isset($map['date'])) {
        $query->whereDate($map['date'], '>=', request('start_date'));
    }

    if (request()->filled('end_date') && isset($map['date'])) {
        $query->whereDate($map['date'], '<=', request('end_date'));
    }

    if (request()->filled('range') && isset($map['range'])) {
        $query->where($map['range'], request('range'));
    }

    if (request()->filled('beat') && isset($map['beat'])) {
        $query->where($map['beat'], request('beat'));
    }

    if (request()->filled('compartment') && isset($map['compartment'])) {
        $query->where($map['compartment'], request('compartment'));
    }

    return $query;
}


}
