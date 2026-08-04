<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $address
 */
class ProductOrder extends Model
{
    protected $table = 'product_orders';
    public $timestamps = false;
    protected $guarded = ['id'];

    static $status = ['pending', 'waiting_delivery', 'shipped', 'success', 'canceled'];
    static $waitingDelivery = 'waiting_delivery';
    static $shipped = 'shipped';
    static $success = 'success';
    static $canceled = 'canceled';
    static $pending = 'pending';

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function sale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    public function gift(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Gift::class, 'gift_id', 'id');
    }
}
