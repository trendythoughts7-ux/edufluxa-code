<?php

namespace App\Services\App;

use App\Models\SpecialOffer;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class BundleFilterService
{
    public function filterBundle($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($teacher_ids) and count($teacher_ids)) {
            $query->whereIn('teacher_id', $teacher_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $query->where('bundles.status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $bundleIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $bundleIdsHasDiscount[] = $ticket->bundle_id;
                        }
                    }

                    $specialOffersBundleIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('bundle_id')
                        ->toArray();

                    $bundleIdsHasDiscount = array_merge($specialOffersBundleIds, $bundleIdsHasDiscount);

                    $query->whereIn('id', $bundleIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.refund_at', DB::raw('count(sales.bundle_id) as sales_count'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'bundles.id', '=', 'sales.bundle_id')
                        ->select('bundles.*', 'sales.bundle_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.bundle_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.bundle_id')
                        ->orderBy('amounts', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'updated_at_asc':
                    $query->orderBy('updated_at', 'asc');
                    break;

                case 'updated_at_desc':
                    $query->orderBy('updated_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        return $query;
    }
}
