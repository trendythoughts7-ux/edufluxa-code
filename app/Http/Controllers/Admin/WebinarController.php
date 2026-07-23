<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WebinarsExport;
use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Jobs\ProcessGenericExport;
use App\Models\Export;
use App\Http\Controllers\Admin\traits\WebinarChangeCreator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Mail\SendNotifications;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\File;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\InstallmentOrder;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\SpecialOffer;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Translation\WebinarTranslation;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class WebinarController extends Controller
{
    use WebinarChangeCreator, ProductBadgeTrait, VideoDemoTrait;


    public function index(Request $request)
    {
        $this->authorize('admin_webinars_list');

        removeContentLocale();

        $type = $request->get('type', 'webinar');
        $query = Webinar::where('webinars.type', $type);

        $totalWebinars = $query->count();
        $totalPendingWebinars = deepClone($query)->where('webinars.status', 'pending')->count();
        $totalDurations = deepClone($query)->sum('duration');
        $totalSales = deepClone($query)->join('sales', 'webinars.id', '=', 'sales.webinar_id')
            ->select(DB::raw('count(sales.webinar_id) as sales_count'))
            ->whereNotNull('sales.webinar_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::getCategories();

        $inProgressWebinars = 0;
        if ($type == 'webinar') {
            $inProgressWebinars = (new \App\Services\App\WebinarFilterService())->getInProgressWebinarsCount();
        }

        $query = (new \App\Services\App\WebinarFilterService())->filterWebinar($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ]);

        $webinars = $query->paginate(10);

        if ($request->get('status', null) == 'active_finished') {
            foreach ($webinars as $key => $webinar) {
                if ($webinar->last_date > time()) { // is in progress
                    unset($webinars[$key]);
                }
            }
        }

        foreach ($webinars as $webinar) {
            $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($webinar, $giftsIds) {
                    $query->where('webinar_id', $webinar->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $webinar->sales = $sales;
        }


        $data = [
            'pageTitle' => trans('admin/pages/webinars.webinars_list_page_title'),
            'webinars' => $webinars,
            'totalWebinars' => $totalWebinars,
            'totalPendingWebinars' => $totalPendingWebinars,
            'totalDurations' => $totalDurations,
            'totalSales' => !empty($totalSales) ? $totalSales->sales_count : 0,
            'categories' => $categories,
            'inProgressWebinars' => $inProgressWebinars,
            'classesType' => $type,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return view('admin.webinars.lists', $data);
    }


    public function create()
    {
        $this->authorize('admin_webinars_create');

        removeContentLocale();

        $teachers = User::where('role_name', Role::$teacher)->get();
        $categories = Category::getCategories();

        $data = [
            'pageTitle' => trans('admin/main.webinar_new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories
        ];

        return view('admin.webinars.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_webinars_create');

        $this->validate($request, [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:webinars,slug',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'summary' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            'duration' => 'required|numeric',
            'start_date' => 'required_if:type,webinar',
            'capacity' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
        ]);

        $result = (new \App\Services\App\WebinarCreationService())->createWebinar($request, $request->all());

        if (is_array($result) and !empty($result['error'])) {
            return back()->withErrors([
                $result['field'] => [$result['message']]
            ]);
        }

        return redirect($result);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::where('id', $id)
            ->with([
                'tickets',
                'sessions',
                'files',
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'filterOptions',
                'prerequisites',
                'quizzes' => function ($query) {
                    $query->with([
                        'quizQuestions' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }
                    ]);
                },
                'webinarPartnerTeacher' => function ($query) {
                    $query->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }]);
                },
                'tags',
                'textLessons',
                'assignments',
                'chapters' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');

                            $query->with([
                                'quiz' => function ($query) {
                                    $query->with([
                                        'quizQuestions' => function ($query) {
                                            $query->orderBy('order', 'asc');
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                },
            ])
            ->first();

        if (empty($webinar)) {
            abort(404);
        }

        $locale = $request->get('locale', getDefaultLocale());
        storeContentLocale($locale, $webinar->getTable(), $webinar->id);

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $teacherQuizzes = Quiz::where('webinar_id', null)
            ->where('creator_id', $webinar->teacher_id)
            ->get();

        $tags = $webinar->tags->pluck('title')->toArray();

        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $webinar->title,
            'categories' => $categories,
            'webinar' => $webinar,
            'webinarCategoryFilters' => !empty($webinar->category) ? $webinar->category->filters : null,
            'webinarFilterOptions' => $webinar->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $webinar->tickets,
            'chapters' => $webinar->chapters,
            'sessions' => $webinar->sessions,
            'files' => $webinar->files,
            'textLessons' => $webinar->textLessons,
            'faqs' => $webinar->faqs,
            'assignments' => $webinar->assignments,
            'teacherQuizzes' => $teacherQuizzes,
            'prerequisites' => $webinar->prerequisites,
            'webinarQuizzes' => $webinar->quizzes,
            'webinarPartnerTeacher' => $webinar->webinarPartnerTeacher,
            'webinarTags' => $tags,
            'defaultLocale' => getDefaultLocale(),
        ];

        return view('admin.webinars.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::find($id);
        $data = $request->all();

        $rules = [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'slug' => 'max:255|unique:webinars,slug,' . $webinar->id,
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'summary' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            'price' => 'nullable|numeric|min:0',
        ];

        if ($webinar->isWebinar()) {
            $rules['start_date'] = 'required|date';
            $rules['duration'] = 'required';
            $rules['capacity'] = 'nullable|numeric|min:0';
        }

        $this->validate($request, $rules);

        $service = new \App\Services\App\WebinarUpdateService();

        $ownershipCheck = $service->checkTeacherOwnership($data, $webinar);
        if ($ownershipCheck !== true) {
            return back()->with(['toast' => $ownershipCheck]);
        }

        $result = $service->updateWebinar($request, $webinar, $data);

        if (is_array($result) and !empty($result['error'])) {
            return back()->withErrors([
                $result['field'] => [$result['message']]
            ]);
        }

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_delete');

        $webinar = Webinar::query()->findOrFail($id);

        $webinar->delete();

        return redirect(getAdminPanelUrl() . '/webinars');
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::query()->findOrFail($id);

        $webinar->update([
            'status' => Webinar::$active
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_approved'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::query()->findOrFail($id);

        $webinar->update([
            'status' => Webinar::$inactive
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_rejected'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
    }

    public function unpublish(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::query()->findOrFail($id);

        $webinar->update([
            'status' => Webinar::$pending
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_status_changes_to_unpublished'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/webinars')->with(['toast' => $toastData]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = Webinar::select('id', 'teacher_id')
            ->where('only_for_students', false)
            ->whereTranslationLike('title', "%$term%");

        if (!empty($option) and $option == 'just_webinar') {
            $query->where('type', Webinar::$webinar);
            $query->where('status', Webinar::$active);
        }

        $webinars = $query->get();

        $result = [];
        foreach ($webinars as $webinar) {
            $result[] = [
                'id' => $webinar->id,
                'title' => $webinar->title,
            ];
        }

        return response()->json($result, 200);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_webinars_export_excel');

        $query = Webinar::query();

        $query = (new \App\Services\App\WebinarFilterService())->filterWebinar($query, $request)
            ->with(['teacher' => function ($qu) {
                $qu->select('id', 'full_name');
            }, 'sales']);

        $webinars = $query->get();

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'webinars',
            'status' => Export::STATUS_PENDING,
        ]);
        ProcessGenericExport::dispatch(WebinarsExport::class, $webinars, $exportRecord->id, 'webinars');
        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }

    public function studentsLists(Request $request, $id)
    {
        $this->authorize('admin_webinar_students_lists');

        $data = (new \App\Services\App\WebinarStudentsService())->getStudentsListData($id, $request);

        if (!empty($data)) {
            return view('admin.webinars.students', $data);
        }

        abort(404);
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $webinar = Webinar::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'webinar' => $webinar
        ];

        return view('admin.webinars.send-notification-to-course-students', $data);
    }


    public function sendNotificationToStudents(Request $request, $id)
    {
        $this->authorize('admin_webinar_notification_to_students');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $studentsCount = (new \App\Services\App\WebinarNotificationService())->sendNotificationToStudents($id, $data);

        if ($studentsCount === null) {
            abort(404);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => $studentsCount]),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl("/webinars/{$id}/students"))->with(['toast' => $toastData]);
    }

    public function orderItems(Request $request)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $result = (new \App\Services\App\WebinarNotificationService())->reorderItems($data);

        if (is_array($result) and !empty($result['error'])) {
            return response([
                'code' => 422,
                'errors' => $result['errors'],
            ], 422);
        }

        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webinar = Webinar::where('id', $id)->first();

        if (!empty($webinar)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($webinar->$relation)) {
                $item = $webinar->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }
}
