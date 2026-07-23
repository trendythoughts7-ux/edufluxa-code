<?php

namespace App\Services\App;

use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Models\Product;
use App\Models\Translation\ProductTranslation;
use App\Models\ProductCategory;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationCategory;
use App\Models\ProductMedia;
use App\Models\ProductSelectedFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductManagementService
{
    use ProductBadgeTrait;

    public function createProduct(Request $request)
    {
        $rules = [
            'creator_id' => 'required|exists:users,id',
            'type' => 'required|in:' . implode(',', Product::$productTypes),
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:products,slug',
            'seo_description' => 'required|max:255',
            'summary' => 'required',
            'description' => 'required',
            'point' => 'nullable|integer',
            'tax' => 'nullable|integer',
            'commission' => 'nullable|integer',
        ];

        Validator::make($request->all(), $rules)->validate();

        $data = $request->all();

        if (empty($data['slug'])) {
            $data['slug'] = Product::makeSlug($data['title']);
        }

        $commission = null;

        if (!empty($data['commission'])) {
            $commission = $data['commission'];

            if ($data['commission_type'] == "fixed_amount") {
                $commission = convertPriceToDefaultCurrency($commission);
            }
        }

        $product = Product::create([
            'creator_id' => $data['creator_id'],
            'type' => $data['type'],
            'slug' => $data['slug'],
            'category_id' => null,
            'price' => null,
            'unlimited_inventory' => false,
            'ordering' => (!empty($data['ordering']) and $data['ordering'] == 'on'),
            'inventory' => null,
            'inventory_warning' => null,
            'delivery_fee' => null,
            'delivery_estimated_time' => null,
            'message_for_reviewer' => null,
            'point' => $data['point'] ?? null,
            'tax' => $data['tax'] ?? null,
            'commission_type' => $data['commission_type'] ?? 'percent',
            'commission' => $commission,
            'status' => Product::$pending,
            'updated_at' => time(),
            'created_at' => time(),
        ]);

        if ($product) {
            ProductTranslation::updateOrCreate([
                'product_id' => $product->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'seo_description' => $data['seo_description'],
                'summary' => $data['summary'],
                'description' => $data['description'],
            ]);

            return getAdminPanelUrl('/store/products/' . $product->id . '/edit?locale=' . $data['locale']);
        }

        return null;
    }

    public function getEditData($id, Request $request)
    {
        $product = Product::where('id', $id)
            ->with([
                'creator',
                'files' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'category' => function ($query) {
                    $query->with([
                        'filters' => function ($query) {
                            $query->with('options');
                        }
                    ]);
                },
                'selectedSpecifications' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with('specification');
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->first();

        if (empty($product)) {
            abort(404);
        }

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $product->getTable(), $product->id);

        $productCategories = ProductCategory::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $productCategoryFilters = !empty($product->category) ? $product->category->filters : [];

        if (empty($product->category) and !empty($request->old('category_id'))) {
            $category = ProductCategory::where('id', $request->old('category_id'))->first();

            if (!empty($category)) {
                $productCategoryFilters = $category->filters;
            }
        }

        $specificationIds = ProductSpecificationCategory::where('category_id', $product->category_id)
            ->pluck('specification_id')
            ->toArray();

        $productSpecifications = ProductSpecification::whereIn('id', $specificationIds)
            ->get();

        return [
            'pageTitle' => trans('update.edit_product'),
            'product' => $product,
            'productCategoryFilters' => $productCategoryFilters,
            'productCategories' => $productCategories,
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
            'productSpecifications' => $productSpecifications,
        ];
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->all();

        $data['images'] = array_filter($data['images']);

        if (empty($data['images']) or !count($data['images'])) {
            $data['images'] = [];
        }

        $request->merge([
            'images' => $data['images']
        ]);

        $rules = [
            'creator_id' => 'required|exists:users,id',
            'type' => 'required|in:' . implode(',', Product::$productTypes),
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:products,slug,' . $product->id,
            'seo_description' => 'required|max:255',
            'summary' => 'required',
            'description' => 'required',
            'point' => 'nullable|integer',
            'tax' => 'nullable|integer',
            'commission' => 'nullable|integer',
            'inventory' => 'required_without:unlimited_inventory',
            'thumbnail' => 'required',
            'images' => 'required|array|min:1',
            'category_id' => 'required',
        ];

        Validator::make($request->all(), $rules)->validate();

        if (empty($data['slug'])) {
            $data['slug'] = Product::makeSlug($data['title']);
        }

        $data['unlimited_inventory'] = (!empty($data['unlimited_inventory']) and $data['unlimited_inventory'] == 'on');

        $inventory = $data['inventory'];
        $productAvailability = $product->getAvailability();

        if ($inventory != $productAvailability) {
            $data['inventory_updated_at'] = time();
        }

        if (isset($product->salesCountCache)) {
            unset($product->salesCountCache);
        }

        if (isset($product->availabilityCount)) {
            unset($product->availabilityCount);
        }

        $commission = null;

        if (!empty($data['commission'])) {
            $commission = $data['commission'];

            if ($data['commission_type'] == "fixed_amount") {
                $commission = convertPriceToDefaultCurrency($commission);
            }
        }

        $product->update([
            'creator_id' => $data['creator_id'],
            'type' => $data['type'],
            'slug' => $data['slug'],
            'category_id' => $data['category_id'],
            'price' => $data['price'] ?? $product->price,
            'unlimited_inventory' => $data['unlimited_inventory'],
            'ordering' => (!empty($data['ordering']) and $data['ordering'] == 'on'),
            'inventory' => $data['inventory'] ?? null,
            'inventory_warning' => $data['inventory_warning'] ?? null,
            'inventory_updated_at' => $data['inventory_updated_at'] ?? null,
            'delivery_fee' => $data['delivery_fee'] ?? null,
            'delivery_estimated_time' => $data['delivery_estimated_time'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'point' => $data['point'] ?? null,
            'tax' => $data['tax'] ?? null,
            'commission_type' => $data['commission_type'] ?? 'percent',
            'commission' => $commission,
            'status' => $data['status'],
            'updated_at' => time(),
        ]);

        ProductTranslation::updateOrCreate([
            'product_id' => $product->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
            'seo_description' => $data['seo_description'],
            'summary' => $data['summary'],
            'description' => $data['description'],
        ]);

        $this->handleProductImages($product, $data);

        ProductSelectedFilterOption::where('product_id', $product->id)->delete();

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            foreach ($filters as $filter) {
                ProductSelectedFilterOption::create([
                    'product_id' => $product->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        // Product Badge
        $this->handleProductBadges($product, $data);

        return getAdminPanelUrl('/store/products/' . $product->id . '/edit?locale=' . $data['locale']);
    }

    private function handleProductImages($product, $data)
    {
        if (!empty($data['thumbnail'])) {
            ProductMedia::updateOrCreate([
                'creator_id' => $product->creator_id,
                'product_id' => $product->id,
                'type' => ProductMedia::$thumbnail,
            ], [
                'path' => $data['thumbnail'],
                'created_at' => time(),
            ]);
        }

        if (!empty($data['images']) and count($data['images'])) {
            ProductMedia::where('creator_id', $product->creator_id)
                ->where('product_id', $product->id)
                ->where('type', ProductMedia::$image)
                ->delete();

            foreach ($data['images'] as $image) {
                if (!empty($image)) {
                    ProductMedia::create([
                        'creator_id' => $product->creator_id,
                        'product_id' => $product->id,
                        'type' => ProductMedia::$image,
                        'path' => $image,
                        'created_at' => time(),
                    ]);
                }
            }
        }

        if (!empty($data['video_demo'])) {
            ProductMedia::updateOrCreate([
                'creator_id' => $product->creator_id,
                'product_id' => $product->id,
                'type' => ProductMedia::$video,
            ], [
                'path' => $data['video_demo'],
                'created_at' => time(),
            ]);
        }
    }

    public function deleteProduct($id)
    {
        $product = Product::where('id', $id)->first();

        if (!empty($product)) {
            $product->delete();
        }
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'action' => 'validation_failed',
                'errors' => $validator->errors(),
            ];
        }

        $product = Product::where('id', $id)->first();

        if (!empty($product)) {
            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($product->$relation)) {
                $item = $product->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return [
                        'action' => 'success',
                        'item' => $item,
                    ];
                }
            }
        }

        return ['action' => 'not_found'];
    }

    public function changeProductStatus($id, $status, $toastMsgKey)
    {
        $product = Product::query()->findOrFail($id);

        $product->update([
            'status' => $status
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans($toastMsgKey),
            'status' => 'success'
        ];

        return $toastData;
    }
}
