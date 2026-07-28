<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountBundle extends Model
{
    protected $table = 'discount_bundles';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function discount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Discount', 'discount_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }
}
