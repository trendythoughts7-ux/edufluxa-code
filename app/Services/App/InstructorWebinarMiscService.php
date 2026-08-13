<?php

namespace App\Services\App;

use App\Models\Webinar;
use App\Models\Tag;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Ticket;
use App\Models\File;
use App\Models\TextLesson;
use App\Models\Prerequisite;
use App\Models\Faq;
use App\Models\WebinarChapter;
use Illuminate\Http\Request;
use Validator;

class InstructorWebinarMiscService
{
    public function search(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            return response('', 422);
        }

        $term = $request->get('term', null);
        $webinarId = $request->get('webinar_id', null);
        $option = $request->get('option', null);

        if (!empty($term)) {
            $webinars = Webinar::select('id', 'teacher_id')
                ->whereTranslationLike('title', '%' . $term . '%')
                ->where('id', '<>', $webinarId)
                ->with(['teacher' => function ($query) {
                    $query->select('id', 'full_name');
                }])
                //->where('creator_id', $user->id)
                ->get();

            $result = [];
            foreach ($webinars as $item) {
                $result[] = [
                    'id' => $item->id,
                    'title' => $item->title . ' - ' . $item->teacher->full_name,
                ];
            }

            return response()->json($result, 200);
        }

        return response('', 422);
    }

    public function getTags(Request $request, $id)
    {
        $webinarId = $request->get('webinar_id', null);
        if (!empty($webinarId)) {
            $tags = Tag::select('id', 'title')
                ->where('webinar_id', $webinarId)
                ->get();
            return response()->json($tags, 200);
        }
        return response('', 422);
    }
    public function invoice($id)
    {
        $user = auth()->user();
        $sale = Sale::where('buyer_id', $user->id)
            ->where('webinar_id', $id)
            ->where('type', 'webinar')
            ->whereNull('refund_at')
            ->with([
                'order',
                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->first();

        if (!empty($sale)) {
            $webinar = Webinar::where('status', 'active')
                ->where('id', $id)
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
                return view(getTemplate() . '.panel.webinar.invoice', $data);
            }
        }

        abort(404);
    }

    public function purchases(Request $request)
    {
        $user = auth()->user();
        $webinarIds = $user->getPurchasedCoursesIds();

        $query = Webinar::whereIn('id', $webinarIds);

        $allWebinars = deepClone($query)->get();
        $allWebinarsCount = $allWebinars->count();
        $hours = $allWebinars->sum('duration');

        $upComing = 0;
        $time = time();

        foreach ($allWebinars as $webinar) {
            if (!empty($webinar->start_date) and $webinar->start_date > $time) {
                $upComing += 1;
            }
        }

        $onlyNotConducted = $request->get('not_conducted');
        if (!empty($onlyNotConducted)) {
            $query->where('start_date', '>', time());
        }

        $webinars = $query->with([
            'files',
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'category',
            'teacher' => function ($query) {
                $query->select('id', 'full_name');
            },
        ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        foreach ($webinars as $webinar) {
            $sale = Sale::where('buyer_id', $user->id)
                ->whereNotNull('webinar_id')
                ->where('type', 'webinar')
                ->where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->first();

            if (!empty($sale)) {
                $webinar->purchase_date = $sale->created_at;
            }
        }

        $data = [
            'pageTitle' => trans('webinars.webinars_purchases_page_title'),
            'webinars' => $webinars,
            'allWebinarsCount' => $allWebinarsCount,
            'hours' => $hours,
            'upComing' => $upComing
        ];

        return view('design_1.panel.webinar.purchases', $data);
    }

    public function getJoinInfo(Request $request)
    {
        $data = $request->all();
        if (!empty($data['webinar_id'])) {
            $user = auth()->user();

            $checkSale = Sale::where('buyer_id', $user->id)
                ->where('webinar_id', $data['webinar_id'])
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->first();

            if (!empty($checkSale)) {
                $webinar = Webinar::where('status', 'active')
                    ->where('id', $data['webinar_id'])
                    ->first();

                if (!empty($webinar)) {
                    $session = Session::select('id', 'creator_id', 'title', 'date', 'description', 'link', 'zoom_start_link', 'session_api', 'api_secret')
                        ->where('webinar_id', $webinar->id)
                        ->where('date', '>=', time())
                        ->orderBy('date', 'asc')
                        ->first();

                    if (!empty($session)) {
                        $session->date = dateTimeFormat($session->date, 'd F Y , H:i');

                        $session->link = $session->getJoinLink(true);

                        return response()->json([
                            'code' => 200,
                            'session' => $session
                        ], 200);
                    }
                }
            }
        }

        return response()->json([], 422);
    }

    public function getNextSessionInfo($id)
    {
        $user = auth()->user();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (!empty($webinar)) {
            $session = Session::select('id', 'creator_id', 'date', 'link', 'zoom_start_link', 'duration', 'webinar_id', 'session_api', 'api_secret', 'moderator_secret')
                ->where('webinar_id', $webinar->id)
                ->where('date', '>=', time())
                ->orderBy('date', 'asc')
                ->first();

            if (!empty($session)) {
                $session->date = dateTimeFormat($session->date, 'Y-m-d H:i');

                $session->link = $session->getJoinLink(true);
            }

            return response()->json([
                'code' => 200,
                'session' => $session,
                'webinar_id' => $webinar->id,
            ], 200);
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
        if (count($itemIds)) {
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
            }
        }
        return response()->json([
            'code' => 200,
        ], 200);
    }
}
