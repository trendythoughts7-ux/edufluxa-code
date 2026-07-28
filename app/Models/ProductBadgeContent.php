<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBadgeContent extends Model
{
    protected $table = "product_badge_contents";
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    /********
     * Relations
     * ******/

    public function targetable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function badge(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductBadge::class, 'product_badge_id', 'id');
    }


    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Webinar::class, 'targetable_id', 'id');
    }

    public function bundle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'targetable_id', 'id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'targetable_id', 'id');
    }

    public function post(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Blog::class, 'targetable_id', 'id');
    }

    public function upcomingCourse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UpcomingCourse::class, 'targetable_id', 'id');
    }


    /* ==========
     | Helpers
     * ==========*/
    public function getContentItem()
    {
        $item = null;

        switch ($this->targetable_type) {
            case "App\Models\Webinar":
                $item = $this->webinar;
                break;
            case "App\Models\Bundle":
                $item = $this->bundle;
                break;
            case "App\Models\Product":
                $item = $this->product;
                break;
            case "App\Models\Blog":
                $item = $this->post;
                break;
            case "App\Models\UpcomingCourse":
                $item = $this->upcomingCourse;
                break;
        }

        return $item;
    }


    public function getContentType()
    {
        $type = "";

        switch ($this->targetable_type) {
            case "App\Models\Webinar":
                $type = "course";
                break;

            case "App\Models\Bundle":
                $type = "bundle";
                break;

            case "App\Models\Product":
                $type = "product";
                break;

            case "App\Models\Blog":
                $type = "blog_article";
                break;

            case "App\Models\UpcomingCourse":
                $type = "upcoming_course";
                break;
        }

        return $type;
    }

}
