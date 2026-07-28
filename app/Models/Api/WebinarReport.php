<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class WebinarReport extends Model
{
    //
    public function getDetailsAttribute(){
        return [
            'id'=>$this->id ,
            'reason'=>$this->reason ,
            'message'=>$this->message ,
            'created_at'=>$this->created_at ,
            'user'=>$this->user->brief??null ,
            'webinar'=>$this->webinar->brief??null 

        ] ;
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\User', 'user_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'webinar_id', 'id');
    }
}
