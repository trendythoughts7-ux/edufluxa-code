<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumTopicBookmark extends Model
{
    protected $table = 'forum_topic_bookmarks';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function topic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ForumTopic', 'topic_id', 'id');
    }
}
