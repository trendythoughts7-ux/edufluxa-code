<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReferralHistoryExport;
use App\Exports\ReferralUsersExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessGenericExport;
use App\Models\Accounting;
use App\Models\Affiliate;
use App\Models\Export;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReferralController extends Controller
{
    public function history($export = false)
    {
        $this->authorize('admin_referrals_history');

        $affiliatesQuery = Affiliate::query();

        $affiliateUsersCount = deepClone($affiliatesQuery)->groupBy('affiliate_user_id')->count();

        $allAffiliateAmounts = Accounting::where('is_affiliate_amount', true)
            ->where('system', false)
            ->sum('amount');

        $allAffiliateCommissionAmounts = Accounting::where('is_affiliate_commission', true)
            ->where('system', false)
            ->sum('amount');

        $affiliatesQuery = $affiliatesQuery
            ->with([
                'affiliateUser' => function ($query) {
                    $query->select('id', 'full_name', 'role_id', 'role_name');
                },
                'referredUser' => function ($query) {
                    $query->select('id', 'full_name', 'role_id', 'role_name');
                }
            ])
            ->orderBy('created_at', 'desc');

        if ($export) {
            return $affiliatesQuery->get();
        }

        $affiliates = $affiliatesQuery->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.referrals_history'),
            'affiliatesCount' => $affiliates->count(),
            'affiliateUsersCount' => $affiliateUsersCount,
            'allAffiliateAmounts' => $allAffiliateAmounts,
            'allAffiliateCommissionAmounts' => $allAffiliateCommissionAmounts,
            'affiliates' => $affiliates,
        ];

        return view('admin.referrals.history', $data);
    }

    public function users($export = false)
    {
        $this->authorize('admin_referrals_users');


        $affiliatesQuery = Affiliate::query()
            ->with([
                'affiliateUser' => function ($query) {
                    $query->select('id', 'full_name', 'role_id', 'role_name', 'affiliate');
                    $query->with([
                        'affiliateCode',
                        'userGroup'
                    ]);
                },
            ])
            ->groupBy('affiliate_user_id')
            ->orderBy('created_at', 'desc');

        if ($export) {
            return $affiliatesQuery->get();
        }

        $affiliates = $affiliatesQuery->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.users'),
            'affiliates' => $affiliates
        ];

        return view('admin.referrals.users', $data);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_referrals_export');

        $type = $request->get('type', 'history');

        if ($type == 'users') {
            $referrals = $this->users(true);
            $exportClass = ReferralUsersExport::class;
            $exportType = 'referral_users';
        } else {
            $referrals = $this->history(true);
            $exportClass = ReferralHistoryExport::class;
            $exportType = 'referral_history';
        }

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => $exportType,
            'status' => Export::STATUS_PENDING,
        ]);

        ProcessGenericExport::dispatch($exportClass, $referrals, $exportRecord->id, $exportType);

        return redirect()->back()->with('toast', [
            'status' => 'success',
            'title' => 'Export',
            'msg' => 'Your export is processing. You will get a notification when it is ready to download.',
        ]);
    }
}
