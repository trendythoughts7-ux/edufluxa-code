<?php

namespace App\Http\Controllers\Panel;

use App\Enums\UploadSource;
use App\Exports\WebinarStudents;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\Faq;
use App\Models\File;
use App\Models\Gift;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Translation\WebinarTranslation;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\WebinarExtraDescription;
use App\User;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Services\App\PanelWebinarSearchService;
use App\Services\App\PanelWebinarCreateService;
use App\Services\App\PanelWebinarUpdateService;

class WebinarController extends Controller
{
    use VideoDemoTrait;
    protected $panelWebinarSearchService;
    protected $panelWebinarCreateService;
    protected $panelWebinarUpdateService;
    public function __construct(PanelWebinarSearchService $panelWebinarSearchService, PanelWebinarCreateService $panelWebinarCreateService, PanelWebinarUpdateService $panelWebinarUpdateService)
    {
        $this->panelWebinarSearchService = $panelWebinarSearchService;
        $this->panelWebinarCreateService = $panelWebinarCreateService;
        $this->panelWebinarUpdateService = $panelWebinarUpdateService;
    }


    public function create(Request $request)
    {
        return $this->panelWebinarCreateService->create($request);
    }
    public function store(Request $request)
    {
        return $this->panelWebinarCreateService->store($request);
    }
    public function edit(Request $request, $id, $step = 1)
    {
        return $this->panelWebinarUpdateService->edit($request, $id, $step);
    }
    public function update(Request $request, $id)
    {
        return $this->panelWebinarUpdateService->update($request, $id);
    }
    public function destroy(Request $request, $id)
    {
        $this->authorize("panel_webinars_delete");

        $user = auth()->user();

        if (!canDeleteContentDirectly()) {
            if ($request->ajax()) {
                return response()->json([], 422);
            } else {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.it_is_not_possible_to_delete_the_content_directly'),
                    'status' => 'error'
                ];
                return redirect()->back()->with(['toast' => $toastData]);
            }
        }


        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!$webinar) {
            abort(404);
        }

        $webinar->delete();

        return response()->json([
            'code' => 200,
            'redirect_to' => $request->get('redirect_to')
        ], 200);
    }


    protected function storeWebinarMedia(Request $request, $webinar)
    {
        return $this->panelWebinarCreateService->storeWebinarMedia($request, $webinar);
    }
    public function deleteIcon($courseId)
    {
        $this->authorize("panel_webinars_delete");

        $user = auth()->user();
        $webinar = Webinar::where('id', $courseId)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id);
                $query->orWhere('teacher_id', $user->id);
            })
            ->first();

        if (!$webinar) {
            abort(404);
        }

        $webinar->update([
            'icon' => null,
        ]);

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.course_icon_removed_successful'),
        ], 200);
    }


    public function duplicate($id)
    {
        $this->authorize("panel_webinars_duplicate");

        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });

                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            })
            ->first();

        if (!empty($webinar)) {
            $new = $webinar->toArray();

            $title = $webinar->title . ' ' . trans('public.copy');
            $description = $webinar->description;
            $seo_description = $webinar->seo_description;


            $new['created_at'] = time();
            $new['updated_at'] = time();
            $new['status'] = Webinar::$pending;

            $new['slug'] = Webinar::makeSlug($title);

            foreach ($webinar->translatedAttributes as $attribute) {
                unset($new[$attribute]);
            }

            unset($new['translations']);

            $newWebinar = Webinar::create($new);

            WebinarTranslation::updateOrCreate([
                'webinar_id' => $newWebinar->id,
                'locale' => mb_strtolower($webinar->locale),
            ], [
                'title' => $title,
                'description' => $description,
                'seo_description' => $seo_description,
            ]);


            return redirect('/panel/courses/' . $newWebinar->id . '/edit');
        }

        abort(404);
    }

    public function exportStudentsList($id)
    {
        $this->authorize("panel_webinars_export_students_list");

        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });

                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            })
            ->first();

        if (!empty($webinar)) {
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
                ->whereHas('buyer')
                ->with([
                    'buyer' => function ($query) {
                        $query->select('id', 'full_name', 'email', 'mobile');
                    }
                ])->get();

            if (!empty($sales) and !$sales->isEmpty()) {

                foreach ($sales as $sale) {
                    if (!empty($sale->gift_id)) {
                        $gift = $sale->gift;

                        $receipt = $gift->receipt;

                        if (!empty($receipt)) {
                            $sale->buyer = $receipt;
                        } else { /* Gift recipient who has not registered yet */
                            $newUser = new User();
                            $newUser->full_name = $gift->name;
                            $newUser->email = $gift->email;

                            $sale->buyer = $newUser;
                        }
                    }
                }

                $export = new WebinarStudents($sales);
                return Excel::download($export, trans('panel.users') . '.xlsx');
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('webinars.export_list_error_not_student'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        return $this->panelWebinarSearchService->search($request);
    }

    public function getTags(Request $request, $id)
    {
        return $this->panelWebinarSearchService->getTags($request, $id);
    }


    public function invoice($webinarId, $saleId)
    {
        $this->authorize("panel_webinars_invoice");

        $user = auth()->user();

        $giftIds = Gift::query()
            ->where(function ($query) use ($user) {
                $query->where('email', $user->email);
                $query->orWhere('user_id', $user->id);
            })
            ->where('status', 'active')
            ->where('webinar_id', $webinarId)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')->toArray();

        $sale = Sale::query()
            ->where('id', $saleId)
            ->where(function ($query) use ($webinarId, $user, $giftIds) {
                $query->where(function ($query) use ($webinarId, $user) {
                    $query->where('buyer_id', $user->id);
                    $query->where('webinar_id', $webinarId);
                });

                if (!empty($giftIds)) {
                    $query->orWhereIn('gift_id', $giftIds);
                }
            })
            ->whereNull('refund_at')
            ->with([
                'order',
                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->first();

        if (!empty($sale)) {

            if (!empty($sale->gift_id)) {
                $gift = $sale->gift;

                $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
            }

            $webinar = Webinar::where('status', 'active')
                ->where('id', $webinarId)
                ->with([
                    'teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'creator' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'webinarPartnerTeacher' => function ($query) {
                        $query->with([
                            'teacher' => function ($query) {
                                $query->select('id', 'full_name');
                            },
                        ]);
                    }
                ])
                ->first();

            if (!empty($webinar)) {
                $data = [
                    'pageTitle' => trans('webinars.invoice_page_title'),
                    'sale' => $sale,
                    'webinar' => $webinar
                ];

                return view('design_1.panel.webinars.invoice.index', $data);
            }
        }

        abort(404);
    }

    public function getNextSessionInfo($id)
    {
        $user = auth()->user();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });

                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            })->first();

        if (!empty($webinar)) {
            $session = Session::where('webinar_id', $webinar->id)
                ->where('date', '>=', time())
                ->orderBy('date', 'asc')
                ->where('status', Session::$Active)
                ->whereDoesntHave('agoraHistory', function ($query) {
                    $query->whereNotNull('end_at');
                })
                ->first();

            if (!empty($session) and $session->title) {

                $session->link = $session->getJoinLink(true);

                if (!empty($session->agora_settings)) {
                    $session->agora_settings = json_decode($session->agora_settings);
                }
            }

            $data = [
                'session' => $session,
                'course' => $webinar
            ];

            $html = view()->make("design_1.panel.webinars.modals.join_next_session_modal", $data)->render();

            return response()->json([
                'code' => 200,
                'html' => $html,
                'join_url' => !empty($session) ? $session->link : '',
            ]);
        }

        return response()->json([], 422);
    }

    public function orderItems(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tableName = $data['table'];
        $itemIds = explode(',', $data['items']);

        if (!is_array($itemIds) and !empty($itemIds)) {
            $itemIds = [$itemIds];
        }

        if (!empty($itemIds) and is_array($itemIds) and count($itemIds)) {
            switch ($tableName) {
                case 'tickets':
                    foreach ($itemIds as $order => $id) {
                        Ticket::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'sessions':
                    foreach ($itemIds as $order => $id) {
                        Session::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'files':
                    foreach ($itemIds as $order => $id) {
                        File::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'text_lessons':
                    foreach ($itemIds as $order => $id) {
                        TextLesson::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'prerequisites':
                    $webinarIds = $user->webinars()->pluck('id')->toArray();

                    foreach ($itemIds as $order => $id) {
                        Prerequisite::where('id', $id)
                            ->whereIn('webinar_id', $webinarIds)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'faqs':
                    foreach ($itemIds as $order => $id) {
                        Faq::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapters':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapter::where('id', $id)
                            ->where('user_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapter_items':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapterItem::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                case 'bundle_webinars':
                    foreach ($itemIds as $order => $id) {
                        BundleWebinar::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;

                case 'webinar_extra_descriptions_learning_materials':
                    foreach ($itemIds as $order => $id) {
                        WebinarExtraDescription::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->where('type', 'learning_materials')
                            ->update(['order' => ($order + 1)]);
                    }
                    break;

                case 'webinar_extra_descriptions_company_logos':
                    foreach ($itemIds as $order => $id) {
                        WebinarExtraDescription::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->where('type', 'company_logos')
                            ->update(['order' => ($order + 1)]);
                    }
                    break;

                case 'webinar_extra_descriptions_requirements':
                    foreach ($itemIds as $order => $id) {
                        WebinarExtraDescription::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->where('type', 'requirements')
                            ->update(['order' => ($order + 1)]);
                    }
                    break;

            }
        }

        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        return $this->panelWebinarSearchService->getContentItemByLocale($request, $id);
    }
}
