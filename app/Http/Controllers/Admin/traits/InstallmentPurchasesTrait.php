<?php

namespace App\Http\Controllers\Admin\traits;


use App\Exports\InstallmentPurchasesExport;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\Export;
use App\Jobs\ProcessGenericExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

trait InstallmentPurchasesTrait
{
    public function purchases()
    {
        $this->authorize('admin_installments_purchases');

        $orders = InstallmentOrder::query()
            ->where('status', '!=', 'paying')
            ->orderBy('created_at', 'desc')
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with(['steps']);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])
            ->paginate(10);

        $orders = $this->handlePurchasedOrders($orders);

        $data = [
            'pageTitle' => trans('update.purchases'),
            'orders' => $orders
        ];

        return view('admin.financial.installments.purchases.index', $data);
    }

    private function handlePurchasedOrders($orders)
    {
        foreach ($orders as $order) {
            $overdueOrderInstallments = $this->getOverdueOrderInstallments($order);
            $getUpcomingInstallment = $this->getUpcomingInstallment($order);

            $order->overdue_count = $overdueOrderInstallments['total'];
            $order->overdue_amount = $overdueOrderInstallments['amount'];
            $order->upcoming_date = !empty($getUpcomingInstallment) ? dateTimeFormat((($getUpcomingInstallment->deadline * 86400) + $order->created_at), 'j M Y') : '';

            $lastStep = $order->selectedInstallment->steps()->orderBy('deadline','desc')->first();

            $order->days_left = 0;

            if (!empty($lastStep)) {
                $dueAt = (($lastStep->deadline * 86400) + $order->created_at);
                $daysLeft = ($dueAt - time()) / 86400;

                if ($daysLeft > 0) {
                    $order->days_left = (int)$daysLeft;
                }
            }
        }

        return $orders;
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->selectedInstallment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('selected_installment_step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->selectedInstallment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('selected_installment_step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }

    /**
     * Fix (added Session 011): precompute a flat stdClass row for the export,
     * resolving every relationship/computed value BEFORE the model goes
     * through queue serialization. This mirrors the Session 007 fix applied
     * to InstallmentVerificationRequestsTrait — dynamic properties set on an
     * Eloquent model (overdue_count, overdue_amount, upcoming_date, days_left)
     * are silently dropped when the model is re-hydrated from the queue, so
     * they must be captured as plain scalar properties on a stdClass instead.
     */
    /**
     * @param \App\Models\InstallmentOrder $order
     */
    private function buildPurchasesExportRow($order)
    {
        $product = '';
        $productType = '';

        if (!empty($order->webinar_id)) {
            $product = $order->webinar->title;
            $productType = trans('update.target_types_courses');
        } elseif (!empty($order->bundle_id)) {
            $product = $order->bundle->title;
            $productType = trans('update.target_types_bundles');
        } elseif (!empty($order->product_id)) {
            $product = $order->product->title;
            $productType = trans('update.target_types_store_products');
        } elseif (!empty($order->subscribe_id)) {
            $product = trans('admin/main.purchased_subscribe');
            $productType = trans('update.target_types_subscription_packages');
        } elseif (!empty($order->registration_package_id)) {
            $product = trans('update.purchased_registration_package');
            $productType = trans('update.target_types_registration_packages');
        }

        $upfront = '--';

        $selectedInstallment = $order->selectedInstallment;

        if (!empty($selectedInstallment->upfront)) {
            $upfront = ($selectedInstallment->upfront_type == 'percent') ? $selectedInstallment->upfront . '%' : handlePrice($selectedInstallment->upfront);
        }

        $stepsFixedAmount = $selectedInstallment->steps->where('amount_type', 'fixed_amount')->sum('amount');
        $stepsPercents = $selectedInstallment->steps->where('amount_type', 'percent')->sum('amount');
        $installmentsAmount = ($stepsFixedAmount ? handlePrice($stepsFixedAmount) : '') . ($stepsPercents ? (($stepsFixedAmount ? ' + ' : '') . $stepsPercents . '%') : '');

        $status = "";
        if ($order->status == "pending_verification") {
            $status = trans('update.pending_verification');
        } elseif ($order->status == "open") {
            $status = trans('admin/main.open');
        } elseif ($order->status == "rejected") {
            $status = trans('public.rejected');
        } elseif ($order->status == "canceled") {
            $status = trans('public.canceled');
        } elseif ($order->status == "refunded") {
            $status = trans('update.refunded');
        }

        $row = new \stdClass();
        $row->user_display = $order->user->id . ' - ' . $order->user->full_name;
        $row->user_mobile = $order->user->mobile;
        $row->user_email = $order->user->email;
        $row->installment_title = $selectedInstallment->installment->title;
        $row->target_type_label = trans('update.target_types_' . $selectedInstallment->installment->target_type);
        $row->product = $product;
        $row->product_type = $productType;
        $row->purchase_date = dateTimeFormat($order->created_at, 'j M Y');
        $row->total_amount = handlePrice($order->getCompletePrice());
        $row->upfront = $upfront;
        $row->installments_count = $selectedInstallment->steps_count;
        $row->installments_amount = $installmentsAmount;
        $row->overdue_count = $order->overdue_count;
        $row->overdue_amount = handlePrice($order->overdue_amount);
        $row->upcoming_date = $order->upcoming_date;
        $row->days_left = $order->days_left;
        $row->status = $status;

        return $row;
    }

    public function purchasesExportExcel(Request $request)
    {
        $this->authorize('admin_installments_purchases');

        $orders = InstallmentOrder::query()
            ->where('status', '!=', 'paying')
            ->orderBy('created_at', 'desc')
            ->with([
                'selectedInstallment' => function ($query) {
                    $query->with(['steps']);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])
            ->get();

        $orders = $this->handlePurchasedOrders($orders);

        $exportRows = $orders->map(function ($order) {
            return $this->buildPurchasesExportRow($order);
        })->values();

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'installment_purchases',
            'status' => Export::STATUS_PENDING,
        ]);
        ProcessGenericExport::dispatch(InstallmentPurchasesExport::class, $exportRows, $exportRecord->id, 'purchases');
        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }

}
