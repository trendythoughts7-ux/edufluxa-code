<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BulkImport extends Model
{
    protected $table = 'bulk_imports';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $dateFormat = 'U';


    /* ==========
     | Relations
     * ==========*/
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    /* ==========
     | Helpers
     * ==========*/


}
