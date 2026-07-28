<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountMeetingPackage extends Model
{
    protected $table = 'discount_meeting_packages';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function discount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Discount', 'discount_id', 'id');
    }

    public function meetingPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\MeetingPackage', 'meeting_package_id', 'id');
    }
}
