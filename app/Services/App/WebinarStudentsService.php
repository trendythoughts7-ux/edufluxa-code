<?php

namespace App\Services\App;

use App\Http\Controllers\Panel\WebinarStatisticController;
use App\Models\Gift;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\InstallmentOrder;
use App\Models\Role;
use App\Models\Webinar;
use App\User;
use Illuminate\Support\Facades\DB;

class WebinarStudentsService
{
    public function getStudentsListData($id, $request)
    {
        $webinar = Webinar::where('id', $id)
            ->with([
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'chapters' => function ($query) {
                    $query->where('status', 'active');
                },
                'sessions' => function ($query) {
                    $query->where('status', 'active');
                },
                'assignments' => function ($query) {
                    $query->where('status', 'active');
                },
                'quizzes' => function ($query) {
                    $query->where('status', 'active');
                },
                'files' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->first();

        if (empty($webinar)) {
            return null;
        }

        $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();

        $installmentSalesIds = [];
        $installmentOrders = InstallmentOrder::query()
            ->where('webinar_id', $webinar->id)
            ->where('status', 'open')
            ->get();
        foreach ($installmentOrders as $installmentOrder) {
            $salesId = $installmentOrder->payments->pluck('sale_id')->toArray();
            $installmentSalesIds = array_merge($installmentSalesIds, $salesId);
        }

        $query = User::join('sales', 'sales.buyer_id', 'users.id')
            ->leftJoin('webinar_reviews', function ($query) use ($webinar) {
                $query->on('webinar_reviews.creator_id', 'users.id')
                    ->where('webinar_reviews.webinar_id', $webinar->id);
            })
            ->select('users.*', 'webinar_reviews.rates', 'sales.access_to_purchased_item', 'sales.id as sale_id', 'sales.gift_id', DB::raw('min(sales.created_at) as purchase_date'))
            ->where(function ($query) use ($webinar, $giftsIds, $installmentSalesIds) {
                $query->where('sales.webinar_id', $webinar->id);
                $query->orWhereIn('sales.gift_id', $giftsIds);
                $query->orWhereIn('sales.id', $installmentSalesIds);
            })
            ->groupBy('sales.buyer_id')
            ->whereNull('sales.refund_at');

        $students = $this->studentsListsFilters($webinar, $query, $request)
            ->orderBy('sales.created_at', 'desc')
            ->paginate(10);

        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpireStudents = 0;
        if (!empty($webinar->access_days)) {
            $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

            $totalExpireStudents = User::join('sales', 'sales.buyer_id', 'users.id')
                ->select('users.*', DB::raw('sales.created_at as purchase_date'))
                ->where(function ($query) use ($webinar, $giftsIds) {
                    $query->where('sales.webinar_id', $webinar->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()])
                ->whereNull('sales.refund_at')
                ->count();
        }

        $webinarStatisticController = new WebinarStatisticController();

        $allStudentsIds = User::join('sales', 'sales.buyer_id', 'users.id')
            ->select('users.*', DB::raw('sales.created_at as purchase_date'))
            ->where(function ($query) use ($webinar, $giftsIds) {
                $query->where('sales.webinar_id', $webinar->id);
                $query->orWhereIn('sales.gift_id', $giftsIds);
            })
            ->whereNull('sales.refund_at')
            ->pluck('id')
            ->toArray();

        $learningPercents = [];
        foreach ($allStudentsIds as $studentsId) {
            $learningPercents[$studentsId] = $webinarStatisticController->getCourseProgressForStudent($webinar, $studentsId);
        }

        foreach ($students as $key => $student) {
            if (!empty($student->gift_id)) {
                $gift = Gift::query()->where('id', $student->gift_id)->first();

                if (!empty($gift)) {
                    $receipt = $gift->receipt;

                    if (!empty($receipt)) {
                        $receipt->rates = $student->rates;
                        $receipt->access_to_purchased_item = $student->access_to_purchased_item;
                        $receipt->sale_id = $student->sale_id;
                        $receipt->purchase_date = $student->purchase_date;
                        $receipt->learning = $webinarStatisticController->getCourseProgressForStudent($webinar, $receipt->id);

                        $learningPercents[$student->id] = $receipt->learning;

                        $students[$key] = $receipt;
                    } else {
                        $newUser = new User();
                        $newUser->full_name = $gift->name;
                        $newUser->email = $gift->email;
                        $newUser->rates = 0;
                        $newUser->access_to_purchased_item = $student->access_to_purchased_item;
                        $newUser->sale_id = $student->sale_id;
                        $newUser->purchase_date = $student->purchase_date;
                        $newUser->learning = 0;

                        $students[$key] = $newUser;
                    }
                }
            } else {
                $student->learning = !empty($learningPercents[$student->id]) ? $learningPercents[$student->id] : 0;
            }
        }

        $roles = Role::all();

        return [
            'pageTitle' => trans('admin/main.students'),
            'webinar' => $webinar,
            'students' => $students,
            'userGroups' => $userGroups,
            'roles' => $roles,
            'totalStudents' => $students->total(),
            'totalActiveStudents' => $students->total() - $totalExpireStudents,
            'totalExpireStudents' => $totalExpireStudents,
            'averageLearning' => count($learningPercents) ? round(array_sum($learningPercents) / count($learningPercents), 2) : 0,
        ];
    }

    public function studentsListsFilters($webinar, $query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $role_id = $request->get('role_id');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'sales.created_at');

        if (!empty($full_name)) {
            $query->where('users.full_name', 'like', "%$full_name%");
        }

        if (!empty($sort)) {
            if ($sort == 'rate_asc') {
                $query->orderBy('webinar_reviews.rates', 'asc');
            }

            if ($sort == 'rate_desc') {
                $query->orderBy('webinar_reviews.rates', 'desc');
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($role_id)) {
            $query->where('users.role_id', $role_id);
        }

        if (!empty($status)) {
            if ($status == 'expire' and !empty($webinar->access_days)) {
                $accessTimestamp = $webinar->access_days * 24 * 60 * 60;

                $query->whereRaw('sales.created_at + ? < ?', [$accessTimestamp, time()]);
            }
        }

        return $query;
    }
}
