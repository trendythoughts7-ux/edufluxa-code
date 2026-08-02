<?php

namespace App\Models;

use App\Mixins\Cart\CartItemInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int|string|null $uid
 * @property array|null $itemInfo
 * @property \App\Models\Ticket|null $ticket
 * @property int|null $quantity
 */
class Cart extends Model
{
    protected $table = "cart";

    public $timestamps = false;

    protected $guarded = ['id'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }

    public function eventTicket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\EventTicket', 'event_ticket_id', 'id');
    }

    public function reserveMeeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ReserveMeeting', 'reserve_meeting_id', 'id');
    }

    public function meetingPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\MeetingPackage', 'meeting_package_id', 'id');
    }

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function productOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id', 'id');
    }

    public function subscribe(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Subscribe', 'subscribe_id', 'id');
    }

    public function promotion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Promotion', 'promotion_id', 'id');
    }

    public function installmentPayment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InstallmentOrderPayment::class, 'installment_payment_id', 'id');
    }

    public function gift(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Gift::class, 'gift_id', 'id');
    }

    public static function emptyCart($userId)
    {
        Cart::where('creator_id', $userId)->delete();
    }

    public function getId()
    {
        return (!empty($this->id) ? $this->id : $this->uid);
    }

    public static function getCartsTotalPrice($carts)
    {
        $totalPrice = 0;

        if (!empty($carts) and count($carts)) {
            foreach ($carts as $cart) {
                $totalPrice += self::getItemPrice($cart);
            }
        }

        return $totalPrice;
    }

    public static function getItemPrice(Cart $cart)
    {
        $price = 0;

        if ((!empty($cart->ticket_id) or !empty($cart->special_offer_id)) and !empty($cart->webinar)) {
            $price += $cart->webinar->price - $cart->webinar->getDiscount($cart->ticket);
        } else if (!empty($cart->webinar_id) and !empty($cart->webinar)) {
            $price += $cart->webinar->price;
        } else if (!empty($cart->bundle_id) and !empty($cart->bundle)) {
            $price += $cart->bundle->price;
        } else if (!empty($cart->event_ticket_id) and !empty($cart->eventTicket)) {
            $price += $cart->eventTicket->getPriceWithDiscount() * $cart->quantity;
        } else if (!empty($cart->meeting_package_id) and !empty($cart->meetingPackage)) {
            $price += $cart->meetingPackage->getPrices()['price'];
        } else if (!empty($cart->reserve_meeting_id) and !empty($cart->reserveMeeting)) {
            $price += $cart->reserveMeeting->paid_amount;
        } else if (!empty($cart->product_order_id) and !empty($cart->productOrder) and !empty($cart->productOrder->product)) {
            $product = $cart->productOrder->product;

            $price += (($product->price * $cart->productOrder->quantity) - $product->getDiscountPrice());
        }

        return $price;
    }

    public function getItemInfo()
    {
        if (empty($this->itemInfo)) {
            $cartItemInfo = new CartItemInfo();

            $this->itemInfo = $cartItemInfo->getItemInfo($this);
        }

        return $this->itemInfo;
    }
}
