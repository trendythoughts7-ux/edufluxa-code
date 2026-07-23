<?php

namespace App\Services\App;

use App\Models\Bundle;
use App\Models\Gift;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class BundleShowService
{
    public function resolveBundleListStats($query)
    {
        $totalBundles = $query->count();
        $totalPendingBundles = deepClone($query)->where('bundles.status', Bundle::$pending)->count();
        $totalSales = deepClone($query)->join('sales', 'bundles.id', '=', 'sales.bundle_id')
            ->select(DB::raw('count(sales.bundle_id) as sales_count, sum(total_amount) as total_amount'))
            ->whereNotNull('sales.bundle_id')
            ->whereNull('sales.refund_at')
            ->first();

        return [
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
        ];
    }

    public function enrichBundlesWithSales($bundles)
    {
        foreach ($bundles as $bundle) {
            $giftsIds = Gift::query()->where('bundle_id', $bundle->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($bundle, $giftsIds) {
                    $query->where('bundle_id', $bundle->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $bundle->sales = $sales;
        }

        return $bundles;
    }
}
