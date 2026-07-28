<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarPartnerTeacher extends Model
{
    protected $table = 'webinar_partner_teacher';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'teacher_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'webinar_id', 'id');
    }
}

