<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function beats($rangeId)
    {
        return DB::table('site_details')
            ->where('client_id', (int)$rangeId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function compartments($beatId)
    {
        return DB::table('site_geofences')
            ->where('site_id', (int)$beatId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
