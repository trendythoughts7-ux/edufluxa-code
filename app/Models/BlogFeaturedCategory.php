<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogFeaturedCategory extends Model
{
    protected $table = 'blog_featured_categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];


    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id', 'id');
    }


}
