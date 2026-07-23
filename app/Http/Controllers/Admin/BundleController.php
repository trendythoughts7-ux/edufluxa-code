<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Category;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\InstallmentOrder;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\Translation\BundleTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BundleController extends Controller
{
    use ProductBadgeTrait, VideoDemoTrait;

    public function index(Request $request)
    {
        $this->authorize('admin_bundles_list');

        removeContentLocale();

        $query = Bundle::query();

        $bundleListStats = app(\App\Services\App\BundleShowService::class)->resolveBundleListStats($query);
        $totalBundles = $bundleListStats['totalBundles'];
        $totalPendingBundles = $bundleListStats['totalPendingBundles'];
        $totalSales = $bundleListStats['totalSales'];

        $categories = Category::getCategories();

        $query = app(\App\Services\App\BundleFilterService::class)->filterBundle($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->withCount([
                'bundleWebinars'
            ]);

        $bundles = $query->paginate(10);

        $bundles = app(\App\Services\App\BundleShowService::class)->enrichBundlesWithSales($bundles);

        $data = [
            'pageTitle' => trans('update.bundles'),
            'bundles' => $bundles,
            'totalBundles' => $totalBundles,
            'totalPendingBundles' => $totalPendingBundles,
            'totalSales' => $totalSales,
            'categories' => $categories,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return view('admin.bundles.lists', $data);
    }

    public function create()
    {
        $this->authorize('admin_bundles_create');

        removeContentLocale();

        $categories = Category::getCategories();

        $data = [
            'pageTitle' => trans('update.new_bundle'),
            'categories' => $categories
        ];

        return view('admin.bundles.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_bundles_create');

        $this->validate($request, [
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:bundles,slug',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
        ]);

        $data = $request->all();

        $redirectUrl = app(\App\Services\App\BundleCreateService::class)->createBundle($request, $data);

        return redirect($redirectUrl);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::where('id', $id)
            ->with([
                'tickets',
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'tags',
                'bundleWebinars'
            ])
            ->first();

        if (empty($bundle)) {
            abort(404);
        }

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $bundle->getTable(), $bundle->id);

        $categories = Category::getCategories();

        $tags = $bundle->tags->pluck('title')->toArray();

        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $bundle->title,
            'categories' => $categories,
            'bundle' => $bundle,
            'bundleCategoryFilters' => !empty($bundle->category) ? $bundle->category->filters : null,
            'bundleFilterOptions' => $bundle->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $bundle->tickets,
            'faqs' => $bundle->faqs,
            'bundleTags' => $tags,
            'bundleWebinars' => $bundle->bundleWebinars,
        ];

        return view('admin.bundles.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');
        $data = $request->all();

        $bundle = Bundle::find($id);

        $rules = [
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:bundles,slug,' . $bundle->id,
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
        ];

        $this->validate($request, $rules);

        $ownershipCheck = app(\App\Services\App\BundleUpdateService::class)->checkBundleTeacherOwnership($data, $bundle);
        if ($ownershipCheck !== true) {
            return back()->with(['toast' => $ownershipCheck]);
        }

        app(\App\Services\App\BundleUpdateService::class)->updateBundle($request, $bundle, $data);

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_bundles_delete');

        $bundle = Bundle::find($id);

        if (!empty($bundle)) {
            $bundle->delete();
        }

        return redirect(getAdminPanelUrl() . '/bundles');
    }

    public function studentsLists(Request $request, $id)
    {
        $this->authorize('admin_webinar_students_lists');

        $data = app(\App\Services\App\BundleStudentsService::class)->getStudentsListData($id, $request);

        if (!empty($data)) {
            return view('admin.bundles.students', $data);
        }

        abort(404);
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $bundle = Bundle::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'bundle' => $bundle
        ];

        return view('admin.bundles.send-notification-to-course-students', $data);
    }


    public function sendNotificationToStudents(Request $request, $id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $sentCount = app(\App\Services\App\BundleNotificationService::class)->sendNotificationToStudents($id, $data);

        if ($sentCount !== null) {
            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => $sentCount]),
                'status' => 'success'
            ];

            return redirect(getAdminPanelUrl("/bundles/{$id}/students"))->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Bundle::select('id')
            ->whereTranslationLike('title', "%$term%");

        $bundles = $query->get();

        $result = [];
        foreach ($bundles as $bundle) {
            $result[] = [
                'id' => $bundle->id,
                'title' => $bundle->title,
            ];
        }

        return response()->json($result, 200);
    }


    public function approve(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::query()->findOrFail($id);

        $bundle->update([
            'status' => Bundle::$active
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.bundle_status_changes_to_approved'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl("/bundles"))->with(['toast' => $toastData]);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::query()->findOrFail($id);

        $bundle->update([
            'status' => Bundle::$inactive
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.bundle_status_changes_to_rejected'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl("/bundles"))->with(['toast' => $toastData]);
    }

    public function unpublish(Request $request, $id)
    {
        $this->authorize('admin_bundles_edit');

        $bundle = Bundle::query()->findOrFail($id);

        $bundle->update([
            'status' => Bundle::$pending
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.bundle_status_changes_to_unpublished'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl("/bundles"))->with(['toast' => $toastData]);
    }

}
