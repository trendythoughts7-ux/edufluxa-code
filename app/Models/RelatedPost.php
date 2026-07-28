<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedPost extends Model
{
    protected $table = "related_posts";
    public $timestamps = false;
    protected $guarded = ['id'];


    /* ==========
     | Relations
     * ==========*/

    public function targetable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }


    public function post(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Blog::class, 'post_id', 'id');
    }
}
