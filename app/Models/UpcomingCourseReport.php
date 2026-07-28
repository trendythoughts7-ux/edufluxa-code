<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpcomingCourseReport extends Model
{
    protected $table = 'upcoming_course_reports';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function upcomingCourse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\UpcomingCourse', 'upcoming_course_id', 'id');
    }
}
