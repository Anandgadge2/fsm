<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function beats($rangeId)
    {
        if (!$rangeId) return response()->json([]);

        return DB::table('site_details')
            ->where('client_id', (int)$rangeId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function compartments($beatId)
    {
        if (!$beatId) return response()->json([]);

        return DB::table('site_geofences')
            ->where('site_id', (int)$beatId)   // 🔑 STRICT MATCH
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
