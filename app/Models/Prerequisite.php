<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $webinar_title
 * @property bool $required
 */
class Prerequisite extends Model
{
    protected $table = 'prerequisites';
    public $timestamps = false;
    protected $guarded = ['id'];



    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Webinar', 'prerequisite_id', 'id');
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

}
