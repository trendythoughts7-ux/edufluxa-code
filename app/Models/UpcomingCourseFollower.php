<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpcomingCourseFollower extends Model
{
    protected $table = 'upcoming_course_followers';
    public $timestamps = false;

    protected $guarded = ['id'];


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
