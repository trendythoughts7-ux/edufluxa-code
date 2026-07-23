<?php

namespace App\Services\App;

use App\Models\Sale;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use Illuminate\Http\Request;

class InstructorWebinarListingService
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isUser()) {
            abort(404);
        }

        $query = Webinar::where(function ($query) use ($user) {
            if ($user->isTeacher()) {
                $query->where('teacher_id', $user->id);
            } elseif ($user->isOrganization()) {
                $query->where('creator_id', $user->id);
            }
        });

        $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
        $data['pageTitle'] = trans('webinars.webinars_list_page_title');

        return $data;
    }

    public function invitations(Request $request)
    {
        $user = auth()->user();

        $invitedWebinarIds = WebinarPartnerTeacher::where('teacher_id', $user->id)->pluck('webinar_id')->toArray();

        $query = Webinar::where('status', 'active');

        if ($user->isUser()) {
            abort(404);
        }

        $query->whereIn('id', $invitedWebinarIds);

        $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
        $data['pageTitle'] = trans('panel.invited_classes');

        return $data;
    }

    public function organizationClasses(Request $request)
    {
        $user = auth()->user();

        if (!empty($user->organ_id)) {
            $query = Webinar::where('creator_id', $user->organ_id)
                ->where('status', 'active');

            $query = $this->organizationClassesFilters($query, $request);

            $webinars = $query
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('panel.organization_classes'),
                'webinars' => $webinars,
            ];

            return $data;
        }

        abort(404);
    }

    private function organizationClassesFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $type = $request->get('type', null);
        $sort = $request->get('sort', null);
        $free = $request->get('free', null);

        $query = fromAndToDateFilter($from, $to, $query, 'start_date');

        if (!empty($type) and $type != 'all') {
            $query->where('type', $type);
        }

        if (!empty($sort) and $sort != 'all') {
            if ($sort == 'expensive') {
                $query->orderBy('price', 'desc');
            }

            if ($sort == 'inexpensive') {
                $query->orderBy('price', 'asc');
            }

            if ($sort == 'bestsellers') {
                $query->whereHas('sales')
                    ->with('sales')
                    ->get()
                    ->sortBy(function ($qu) {
                        return $qu->sales->count();
                    });
            }

            if ($sort == 'best_rates') {
                $query->with([
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get()
                    ->sortBy(function ($qu) {
                        return $qu->reviews->avg('rates');
                    });
            }
        }

        if (!empty($free) and $free == 'on') {
            $query->where(function ($qu) {
                $qu->whereNull('price')
                    ->orWhere('price', '<', '0');
            });
        }

        return $query;
    }

    private function makeMyClassAndInvitationsData($query, $user, $request)
    {
        $webinarHours = deepClone($query)->sum('duration');

        $onlyNotConducted = $request->get('not_conducted');
        if (!empty($onlyNotConducted)) {
            $query->where('status', 'active')
                ->where('start_date', '>', time());
        }

        $query->with([
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'category',
            'teacher',
            'sales' => function ($query) {
                $query->where('type', 'webinar')
                    ->whereNull('refund_at');
            }
        ])->orderBy('updated_at', 'desc');

        $webinarsCount = $query->count();

        $webinars = $query->paginate(10);

        $webinarSales = Sale::where('seller_id', $user->id)
            ->where('type', 'webinar')
            ->whereNotNull('webinar_id')
            ->whereNull('refund_at')
            ->with('webinar')
            ->get();

        $webinarSalesAmount = 0;
        $courseSalesAmount = 0;
        foreach ($webinarSales as $webinarSale) {
            if ($webinarSale->webinar->type == 'webinar') {
                $webinarSalesAmount += $webinarSale->amount;
            } else {
                $courseSalesAmount += $webinarSale->amount;
            }
        }

        return [
            'webinars' => $webinars,
            'webinarsCount' => $webinarsCount,
            'webinarSalesAmount' => $webinarSalesAmount,
            'courseSalesAmount' => $courseSalesAmount,
            'webinarHours' => $webinarHours,
        ];
    }
}
