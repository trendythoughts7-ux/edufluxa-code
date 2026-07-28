<?php
namespace App\Models\Api ;
use App\Models\Favorite as WebFavorite;

class Favorite extends WebFavorite{

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'webinar_id', 'id');
    }
    
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\User', 'user_id', 'id');
    }
}