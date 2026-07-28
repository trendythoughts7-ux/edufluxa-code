<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany($this, 'section_group_id', 'id');
    }
}
