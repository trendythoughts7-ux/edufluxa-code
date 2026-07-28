<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class BundleWebinar extends Model
{
    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'webinar_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Bundle', 'bundle_id', 'id');
    }
}
