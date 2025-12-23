<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\DB;

trait FilterDataTrait
{
    protected function filterData(): array
    {
        return [
            'ranges' => DB::table('site_details')
                ->select('client_name')
                ->distinct()
                ->orderBy('client_name')
                ->get(),

            'beats' => DB::table('site_details')
                ->select('name')
                ->distinct()
                ->orderBy('name')
                ->get(),

            'geofences' => DB::table('attendance')
                ->select('geo_name')
                ->whereNotNull('geo_name')
                ->distinct()
                ->orderBy('geo_name')
                ->get(),
        ];
    }
}
