<?php

namespace App\Models\Api;

use App\Models\MeetingTime as Model;

class MeetingTime extends Model
{
    //
    public function meeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Meeting', 'meeting_id', 'id');
    }
}
