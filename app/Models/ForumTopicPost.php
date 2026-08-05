<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $replies_count
 */
class ForumTopicPost extends Model
{
    protected $table = 'forum_topic_posts';
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

    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\ForumTopicLike', 'topic_post_id', 'id');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ForumTopicPost', 'parent_id', 'id');
    }

    public function getLikeUrl($forumSlug, $topicSlug)
    {
        return "/forums/{$forumSlug}/topics/{$topicSlug}/posts/{$this->id}/likeToggle";
    }

    public function getEditUrl($forumSlug, $topicSlug)
    {
        return "/forums/{$forumSlug}/topics/{$topicSlug}/posts/{$this->id}/edit";
    }

    public function getAttachmentUrl($forumSlug, $topicSlug)
    {
        return "/forums/{$forumSlug}/topics/{$topicSlug}/posts/{$this->id}/downloadAttachment";
    }

    public function getAttachmentName()
    {
        $name = "";

        if (!empty($this->attach)) {
            $attach = explode('/',$this->attach);

            $name = $attach[array_key_last($attach)];
        }

        return $name;
    }
}
