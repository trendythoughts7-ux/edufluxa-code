<?php

namespace App\Models;

use App\Models\Observers\OrderItemNumberObserver;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int|null $reserve_meeting_id
 */
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Webinar::class, 'webinar_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'id');
    }

    public function subscribe(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subscribe::class, 'subscribe_id', 'id');
    }

    public function promotion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'id');
    }

    public function reserveMeeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ReserveMeeting::class, 'reserve_meeting_id', 'id');
    }

    public function registrationPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RegistrationPackage::class, 'registration_package_id', 'id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function productOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id', 'id');
    }

    public function installmentPayment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InstallmentOrderPayment::class, 'installment_payment_id', 'id');
    }

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
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


    public static function getSeller(OrderItem $orderItem)
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

        if (!empty($this->reserve_meeting_id)) {
            $typeName = "meeting";
        } elseif (!empty($this->product_id)) {
            $typeName = "product";
        } elseif (!empty($this->bundle_id)) {
            $typeName = "bundle";
        } elseif (!empty($this->promotion_id)) {
            $typeName = "promotion";
        } elseif (!empty($this->event_ticket_id)) {
            $typeName = "event_ticket";
        } elseif (!empty($this->meeting_package_id)) {
            $typeName = "meeting_package";
        }

        return $typeName;
    }

}
