<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedCourse extends Model
{
    protected $table = "related_courses";
    public $timestamps = false;
    protected $guarded = ['id'];


    /* ==========
     | Relations
     * ==========*/

    public function targetable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }


    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Webinar::class, 'course_id', 'id');
    }
}
