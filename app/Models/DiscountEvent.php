<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountEvent extends Model
{
    protected $table = 'discount_events';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function discount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Discount', 'discount_id', 'id');
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Event', 'event_id', 'id');
    }
}
