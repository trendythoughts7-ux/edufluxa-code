<?php

namespace App\Http\Controllers\Admin\traits;


use App\Exports\InstallmentVerifiedUsersExport;
use App\Jobs\ProcessGenericExport;
use App\Models\Export;
use App\Models\Accounting;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use App\Models\SelectedInstallmentStep;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

trait InstallmentVerificationRequestsTrait
{
    public function verificationRequests()
    {
        $this->authorize('admin_installments_verification_requests');

        $orders = InstallmentOrder::query()->select('*', DB::raw("case
            when status = 'pending_verification' then 'a'
            when status = 'open' then 'b'
            when status = 'rejected' then 'c'
            when status = 'canceled' then 'd'
            when status = 'refunded' then 'e'
            end as status_order
        "))
            ->where('status', '!=', 'paying')
            ->orderBy('status_order', 'asc')
            ->whereHas('installment', function ($query) {
                $query->where('verification', true);
            })
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with(['steps']);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('update.verification_requests'),
            'orders' => $orders
        ];

        return view('admin.financial.installments.verification_requests', $data);
    }

    public function verifiedUsers()
    {
        $this->authorize('admin_installments_verified_users');

        $users = User::query()->where('installment_approval', true)
            ->paginate(10);

        $users = $this->handleVerifiedUsers($users);

        $data = [
            'pageTitle' => trans('update.verified_users'),
            'users' => $users
        ];

        return view('admin.financial.installments.verified_users', $data);
    }

    private function handleVerifiedUsers($users)
    {
        foreach ($users as $user) {
            $orders = InstallmentOrder::query()->where('user_id', $user->id)
                ->where('status', 'open')
                ->get();

            $totalAmount = 0;
            $unpaidStepsCount = 0;
            $unpaidStepsAmount = 0;
            $overdueCount = 0;
            $overdueAmount = 0;

            foreach ($orders as $order) {
                $itemPrice = $order->getItemPrice();

                $installment = $order->selectedInstallment;

                $totalAmount += $installment->totalPayments($itemPrice);

                $steps = SelectedInstallmentStep::query()
                    ->where('selected_installment_id', $installment->id)
                    ->whereDoesntHave('orderPayment')
                    ->get();

                $unpaidStepsCount += $steps->count();

                foreach ($steps as $step) {
                    $stepAmount = $step->getPrice($itemPrice);
                    $unpaidStepsAmount += $stepAmount;

                    if (($step->deadline * 86400) + $order->created_at < time()) {
                        $overdueCount += 1;
                        $overdueAmount += $stepAmount;
                    }
                }
            }

            $user->totalAmount = $totalAmount;
            $user->unpaidStepsCount = $unpaidStepsCount;
            $user->unpaidStepsAmount = $unpaidStepsAmount;
            $user->overdueCount = $overdueCount;
            $user->overdueAmount = $overdueAmount;
        }

        return $users;
    }

    public function verifiedUsersExportExcel(Request $request)
    {
        $this->authorize('admin_installments_verified_users');

        $users = User::query()->where('installment_approval', true)
            ->get();

        $users = $this->handleVerifiedUsers($users);
        $users = $users->map(function ($user) {
            return (object) [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'purchaseAmounts' => $user->getPurchaseAmounts(),
                'totalAmount' => $user->totalAmount,
                'unpaidStepsCount' => $user->unpaidStepsCount,
                'unpaidStepsAmount' => $user->unpaidStepsAmount,
                'overdueCount' => $user->overdueCount,
                'overdueAmount' => $user->overdueAmount,
            ];
        });

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'installment_verified_users',
            'status' => Export::STATUS_PENDING,
        ]);
        ProcessGenericExport::dispatch(InstallmentVerifiedUsersExport::class, $users, $exportRecord->id, 'verifiedUsers');
        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }
}
