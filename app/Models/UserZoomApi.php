<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserZoomApi extends Model
{
    protected $table = 'users_zoom_api';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'jwt_token' => 'encrypted',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
