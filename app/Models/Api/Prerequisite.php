<?php

namespace App\Models\Api;

use App\Models\Prerequisite as Model;


class Prerequisite extends Model
{
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'prerequisite_id', 'id')
        ->where('status','active')->where('private',false) ;
        ;
    }
}
