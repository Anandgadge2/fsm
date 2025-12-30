<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\DB;

trait FilterDataTrait
{
    /**
     * Resolve Range → Beat → Compartment into SITE IDs
     */
    protected function resolveSiteIds(): array
    {
        $q = DB::table('site_details')->select('site_details.id');

        // Range → client_details.id
        if (request()->filled('range')) {
            $q->where('site_details.client_id', request('range'));
        }

        // Beat → site_details.id
        if (request()->filled('beat')) {
            $q->where('site_details.id', request('beat'));
        }

        // Compartment → site_geofences.id → site_id
        if (request()->filled('compartment')) {
            $q->whereIn('site_details.id', function ($sub) {
                $sub->select('site_id')
                    ->from('site_geofences')
                    ->where('id', request('compartment'));
            });
        }

        return $q->pluck('id')->toArray();
    }

    /**
     * Apply filters safely to ANY query that has site_id
     */
    protected function applyCanonicalFilters($query, string $dateColumn = null, string $siteColumn = 'site_id')
    {
        // Date
        if ($dateColumn) {
            if (request()->filled('start_date')) {
                $query->whereDate($dateColumn, '>=', request('start_date'));
            }
            if (request()->filled('end_date')) {
                $query->whereDate($dateColumn, '<=', request('end_date'));
            }
        }

        // Site filter
        if (
            request()->filled('range') ||
            request()->filled('beat') ||
            request()->filled('compartment')
        ) {
            $siteIds = $this->resolveSiteIds();

            if (empty($siteIds)) {
                // HARD STOP – prevents silent empty bugs
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn($siteColumn, $siteIds);
            }
        }

        return $query;
    }

    /**
     * Data for global filter dropdowns
     */
    public function filterData(): array
    {
        $ranges = DB::table('client_details')
            ->where('isActive', 1)
            ->orderBy('name')
            ->pluck('name', 'id');

        $beats = collect();
        if (request()->filled('range')) {
            $beats = DB::table('site_details')
                ->where('client_id', request('range'))
                ->orderBy('name')
                ->pluck('name', 'id');
        }

        $compartments = collect();
        if (request()->filled('beat')) {
            $compartments = DB::table('site_geofences')
                ->where('site_id', request('beat'))
                ->orderBy('name')
                ->pluck('name', 'id');
        }

        return compact('ranges', 'beats', 'compartments');
    }
}
