<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $usersAvatars
 */
class ForumFeaturedTopic extends Model
{
    protected $table = 'forum_featured_topics';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];


    public function topic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ForumTopic', 'topic_id', 'id');
    }
}
