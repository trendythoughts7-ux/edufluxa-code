<?php

namespace App\Http\Controllers\Admin\Store;

use App\Exports\StoreProductsExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessGenericExport;
use App\Models\Export;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductOrder;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin_store_products');
        removeContentLocale();
        $data = app(\App\Services\App\ProductListingService::class)->getIndexData($request);
        return view('admin.store.products.lists', $data);
    }
    public function inHouseProducts(Request $request)
    {
        $this->authorize('admin_store_in_house_products');
        removeContentLocale();
        $data = app(\App\Services\App\ProductListingService::class)->getInHouseProductsData($request);
        return view('admin.store.products.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_store_new_product');

        $data = [
            'pageTitle' => trans('update.create_new_product'),
        ];

        return view('admin.store.products.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_store_new_product');

        $url = app(\App\Services\App\ProductManagementService::class)->createProduct($request);

        if ($url) {
            return redirect($url);
        }

        return back();
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_store_edit_product');

        $data = app(\App\Services\App\ProductManagementService::class)->getEditData($id, $request);

        return view('admin.store.products.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_store_edit_product');

        $url = app(\App\Services\App\ProductManagementService::class)->updateProduct($request, $id);

        return redirect($url);
    }

    public function destroy($id)
    {
        $this->authorize('admin_store_delete_product');

        app(\App\Services\App\ProductManagementService::class)->deleteProduct($id);

        return back();
    }

    public function search(Request $request)
    {
        $result = app(\App\Services\App\ProductListingService::class)->search($request);
        return response()->json($result, 200);
    }


    public function getContentItemByLocale(Request $request, $id)
    {
        $this->authorize('admin_store_new_product');
        $result = app(\App\Services\App\ProductManagementService::class)->getContentItemByLocale($request, $id);
        if ($result['action'] === 'validation_failed') {
            return response([
                'code' => 422,
                'errors' => $result['errors'],
            ], 422);
        }
        if ($result['action'] === 'success') {
            return response()->json([
                'item' => $result['item']
            ], 200);
        }
        abort(403);
    }


    public function settings()
    {
        $this->authorize('admin_store_settings');
        $data = app(\App\Services\App\ProductSettingsService::class)->getSettings();
        $data['pageTitle'] = trans('update.store_settings');
        return view('admin.store.settings', $data);
    }

    public function storeSettings(Request $request)
    {
        $this->authorize('admin_store_settings');
        app(\App\Services\App\ProductSettingsService::class)->storeSettings($request);
        return back();
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_store_export_products');

        $query = Product::query();

        if (!empty($request->get('in_house_products'))) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

            $query->whereHas('creator', function ($query) use ($adminRoleIds) {
                $query->whereIn('role_id', $adminRoleIds);
            });

        }

        $products = app(\App\Services\App\ProductListingService::class)->handleFilters($query, $request)
            ->with([
                'category',
                'creator' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
            ])->get();

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'store_products',
            'status' => Export::STATUS_PENDING,
        ]);

        ProcessGenericExport::dispatch(StoreProductsExport::class, $products, $exportRecord->id, 'store_products');

        return redirect()->back()->with('toast', [
            'status' => 'success',
            'title' => 'Export',
            'msg' => 'Your export is processing. You will get a notification when it is ready to download.',
        ]);
    }



    public function approve(Request $request, $id)
    {
        $this->authorize('admin_store_edit_product');

        $toastData = app(\App\Services\App\ProductManagementService::class)->changeProductStatus($id, Product::$active, 'update.product_status_changes_to_approved');

        return redirect(getAdminPanelUrl("/store/products"))->with(['toast' => $toastData]);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_store_edit_product');

        $toastData = app(\App\Services\App\ProductManagementService::class)->changeProductStatus($id, Product::$inactive, 'update.product_status_changes_to_rejected');

        return redirect(getAdminPanelUrl("/store/products"))->with(['toast' => $toastData]);
    }

    public function unpublish(Request $request, $id)
    {
        $this->authorize('admin_store_edit_product');

        $toastData = app(\App\Services\App\ProductManagementService::class)->changeProductStatus($id, Product::$pending, 'update.product_status_changes_to_unpublished');

        return redirect(getAdminPanelUrl("/store/products"))->with(['toast' => $toastData]);
    }

}
