<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardProductsController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('products.status', Product::$active)
            ->where('ordering', true)
            ->whereNotNull('point');

        $productController = new ProductController();

        $query = $productController->handleFilters($request, $query, true);

        $listData = $this->getListData($request, $query);

        if ($request->ajax()) {
            return $listData;
        }

        $categories = ProductCategory::whereNull('parent_id')
            ->with([
                'subCategories' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->get();

        $categoryId = $request->get('category_id', null);
        $selectedCategory = null;

        if (!empty($categoryId)) {
            $selectedCategory = ProductCategory::where('id', $categoryId)->first();
        }

        $seoSettings = getSeoMetas('reward_products');
        $pageTitle = $seoSettings['title'] ?? '';
        $pageDescription = $seoSettings['description'] ?? '';
        $pageRobot = getPageRobot('reward_products');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'productCategories' => $categories,
            'selectedCategory' => $selectedCategory,
            'isRewardProducts' => true,
        ];

        $data = array_merge($data, $listData);

        return view(getTemplate() . '.products.search', $data);
    }

    private function getListData(Request $request, $query)
    {
        $page = $request->get('page') ?? 1;
        $count = 9;

        $cloneQuery = deepClone($query);
        $total = DB::table(DB::raw("({$cloneQuery->toSql()}) as sub"))
            ->mergeBindings($cloneQuery->getQuery())
            ->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $products = $query->with([
            'creator' => function ($query) {
                $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
            }
        ])->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $products, $total, $count);
        }

        return [
            'products' => $products,
            'productsCount' => $total,
            'pagination' => $this->makePagination($request, $products, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $products, $total, $count)
    {
        $html = (string)view()->make(getTemplate() . '.products.components.cards.grids.reward_index', [
            'products' => $products,
            'gridCardClassName' => "col-12 col-lg-6 mt-24",
            'withoutStyles' => true,
        ]);

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $products, $total, $count, true),
        ]);
    }
}
