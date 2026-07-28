<?php

namespace App\Models;

use App\Models\Observers\OrderItemNumberObserver;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];


    protected static function boot()
    {
        parent::boot();

        OrderItem::observe(OrderItemNumberObserver::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }

    public function subscribe(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Subscribe', 'subscribe_id', 'id');
    }

    public function promotion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Promotion', 'promotion_id', 'id');
    }

    public function reserveMeeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ReserveMeeting', 'reserve_meeting_id', 'id');
    }

    public function registrationPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\RegistrationPackage', 'registration_package_id', 'id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function productOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ProductOrder', 'product_order_id', 'id');
    }

    public function installmentPayment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InstallmentOrderPayment::class, 'installment_payment_id', 'id');
    }

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Ticket', 'ticket_id', 'id');
    }

    public function gift(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Gift::class, 'gift_id', 'id');
    }

    public function eventTicket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id', 'id');
    }

    public function meetingPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MeetingPackage::class, 'meeting_package_id', 'id');
    }


    /*==========
     | Helpers
     * ========*/


    public static function getSeller($orderItem)
    {
        $seller = null;

        if (!empty($orderItem->webinar_id) and empty($orderItem->promotion_id)) {
            $seller = $orderItem->webinar->creator_id;
        } elseif (!empty($orderItem->reserve_meeting_id)) {
            $seller = $orderItem->reserveMeeting->meeting->creator_id;
        } elseif (!empty($orderItem->product_id)) {
            $seller = $orderItem->product->creator_id;
        } elseif (!empty($orderItem->bundle_id)) {
            $seller = $orderItem->bundle->creator_id;
        } elseif (!empty($orderItem->event_ticket_id)) {
            $seller = $orderItem->eventTicket->event->creator_id;
        } elseif (!empty($orderItem->meeting_package_id)) {
            $seller = $orderItem->meetingPackage->creator_id;
        }

        return $seller;
    }

    public function getItemTypeName()
    {
        $typeName = "course";

        if (!empty($orderItem->reserve_meeting_id)) {
            $typeName = "meeting";
        } elseif (!empty($orderItem->product_id)) {
            $typeName = "product";
        } elseif (!empty($orderItem->bundle_id)) {
            $typeName = "bundle";
        } elseif (!empty($orderItem->promotion_id)) {
            $typeName = "promotion";
        } elseif (!empty($orderItem->event_ticket_id)) {
            $typeName = "event_ticket";
        } elseif (!empty($orderItem->meeting_package_id)) {
            $typeName = "meeting_package";
        }

        return $typeName;
    }

}
