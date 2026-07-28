<?php

namespace App\Models\Api;

use App\Models\Follow as Model;

class Follow extends Model
{


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\User', 'user_id', 'id');
    }

    public function userFollower(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\User', 'follower', 'id');
    }
}
