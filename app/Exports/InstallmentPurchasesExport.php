<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstallmentPurchasesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            trans('admin/main.user'),
            trans('admin/main.mobile'),
            trans('admin/main.email'),
            trans('admin/main.title'),
            trans('update.target'),
            trans('update.product'),
            trans('update.target_type'),
            trans('panel.purchase_date'),
            trans('financial.total_amount'),
            trans('update.upfront'),
            trans('update.installments_count'),
            trans('update.installments_amount'),
            trans('update.overdue'),
            trans('update.overdue_amount'),
            trans('update.first_unpaid_installment_date'),
            trans('update.days_left'),
            trans('admin/main.status'),
        ];
    }

    /**
     * Fix (Session 011): $order here is now a flat stdClass row built by
     * InstallmentPurchasesTrait::buildPurchasesExportRow() BEFORE the queue
     * dispatch, not a raw Eloquent model. All values are plain scalars that
     * survive queue serialization intact — no relationship access, no
     * previously-set dynamic properties that could be silently dropped.
     *
     * @inheritDoc
     */
    public function map($order): array
    {
        return [
            $order->user_display,
            $order->user_mobile,
            $order->user_email,
            $order->installment_title,
            $order->target_type_label,
            $order->product,
            $order->product_type,
            $order->purchase_date,
            $order->total_amount,
            $order->upfront,
            $order->installments_count,
            $order->installments_amount,
            $order->overdue_count,
            $order->overdue_amount,
            $order->upcoming_date,
            $order->days_left,
            $order->status
        ];
    }
}
