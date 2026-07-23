<?php

namespace App\Services\App;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDiscount;
use App\Models\ProductOrder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ProductListingService
{
    public function getIndexData($request)
    {
        removeContentLocale();

        $query = Product::query();

        $topStatData = $this->getTopPageStats(deepClone($query));

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'creator' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
            ]);

        $products = $query->paginate(10);

        $categories = ProductCategory::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $data = [
            'pageTitle' => trans('update.products'),
            'products' => $products,
            'categories' => $categories,
        ];

        $data = array_merge($data, $topStatData);

        return $data;
    }

    public function getInHouseProductsData($request)
    {
        removeContentLocale();

        $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

        $query = Product::query()
            ->whereHas('creator', function ($query) use ($adminRoleIds) {
                $query->whereIn('role_id', $adminRoleIds);
            });

        $topStatData = $this->getTopPageStats(deepClone($query));

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'creator' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
            ]);

        $products = $query->paginate(10);

        $categories = ProductCategory::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $data = [
            'pageTitle' => trans('update.in-house-products'),
            'products' => $products,
            'categories' => $categories,
            'inHouseProducts' => true
        ];

        $data = array_merge($data, $topStatData);

        return $data;
    }

    public function getTopPageStats($query)
    {
        $totalPhysicalProducts = deepClone($query)->where('type', Product::$physical)->count();
        $totalPhysicalSales = deepClone($query)->where('type', Product::$physical)
            ->join('product_orders', 'products.id', 'product_orders.product_id')
            ->select(DB::raw('sum(quantity) as salesCount'))
            ->whereNotNull('product_orders.sale_id')
            ->whereNotIn('product_orders.status', [ProductOrder::$canceled, ProductOrder::$pending])
            ->first();

        $totalVirtualProducts = deepClone($query)->where('type', Product::$virtual)->count();
        $totalVirtualSales = deepClone($query)->where('type', Product::$virtual)
            ->join('product_orders', 'products.id', 'product_orders.product_id')
            ->select(DB::raw('sum(quantity) as salesCount'))
            ->whereNotNull('product_orders.sale_id')
            ->whereNotIn('product_orders.status', [ProductOrder::$canceled, ProductOrder::$pending])
            ->first();

        $totalSellers = deepClone($query)->groupBy('creator_id')->get()->count();

        $totalBuyers = deepClone($query)
            ->join('product_orders', 'products.id', 'product_orders.product_id')
            ->select(DB::raw('count(buyer_id) as buyerCount'))
            ->whereNotNull('product_orders.sale_id')
            ->whereNotIn('product_orders.status', [ProductOrder::$canceled, ProductOrder::$pending])
            ->groupBy('buyer_id')
            ->first();

        return [
            'totalPhysicalProducts' => $totalPhysicalProducts,
            'totalPhysicalSales' => !empty($totalPhysicalSales) ? $totalPhysicalSales->salesCount : 0,
            'totalVirtualProducts' => $totalVirtualProducts,
            'totalVirtualSales' => !empty($totalVirtualSales) ? $totalVirtualSales->salesCount : 0,
            'totalSellers' => $totalSellers,
            'totalBuyers' => !empty($totalBuyers) ? $totalBuyers->buyerCount : 0,
        ];
    }

    public function handleFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $creator_ids = $request->get('creator_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($creator_ids) and count($creator_ids)) {
            $query->whereIn('creator_id', $creator_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $query->where('products.status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();

                    $productIdsHasDiscount = ProductDiscount::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->pluck('product_id')
                        ->toArray();

                    $query->whereIn('id', $productIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.refund_at', DB::raw('count(sales.product_order_id) as sales_count'))
                        ->whereNotNull('sales.product_order_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.refund_at', DB::raw('count(sales.product_order_id) as sales_count'))
                        ->whereNotNull('sales.product_order_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->orderBy('sales_count', 'desc');

                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.product_order_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.product_order_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
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

                case 'inventory_asc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.refund_at', DB::raw('(products.inventory - sum(product_orders.quantity)) as remaining_inventory'))
                        ->whereNotNull('products.inventory')
                        ->whereNotNull('product_orders.sale_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->orderBy('remaining_inventory', 'asc');

                    break;

                case 'inventory_desc':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.refund_at', DB::raw('(products.inventory - sum(product_orders.quantity)) as remaining_inventory'))
                        ->whereNotNull('products.inventory')
                        ->whereNotNull('product_orders.sale_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->orderBy('remaining_inventory', 'desc');

                    break;

                case 'no_inventory':
                    $query->join('product_orders', 'products.id', '=', 'product_orders.product_id')
                        ->leftJoin('sales', function ($join) {
                            $join->on('product_orders.id', '=', 'sales.product_order_id')
                                ->whereNull('sales.refund_at');
                        })
                        ->select('products.*', 'sales.product_order_id', 'sales.refund_at', DB::raw('(products.inventory - sum(product_orders.quantity)) as remaining_inventory'))
                        ->whereNotNull('products.inventory')
                        ->whereNotNull('product_orders.sale_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('products.id')
                        ->havingRaw("remaining_inventory < 1");

                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function search($request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Product::select('id')
            ->whereTranslationLike('title', "%$term%");

        if (!empty($option)) {

        }

        $products = $query->get();

        $result = [];
        foreach ($products as $item) {
            $result[] = [
                'id' => $item->id,
                'title' => $item->title,
            ];
        }

        return $result;
    }
}
