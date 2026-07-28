<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseNoticeboard extends Model
{
    protected $table = 'course_noticeboards';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $colors = ['warning', 'danger', 'neutral', 'info', 'success'];

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }

    public function noticeboardStatus(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\CourseNoticeboardStatus', 'noticeboard_id', 'id');
    }

    public function getIcon()
    {
        $icons = [
            'warning' => 'danger',
            'danger' => 'close-circle',
            'neutral' => 'more-circle',
            'info' => 'info-circle',
            'success' => 'tick-circle'
        ];

        return $icons[$this->color];
    }
}
