<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BecomeInstructor extends Model
{
    protected $table = 'become_instructors';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function registrationPackage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\RegistrationPackage', 'package_id', 'id');
    }


    public function sendNotificationToUser($status)
    {
        $notifyOptions = [
            '[u.role]' => $this->role == 'teacher' ? trans('admin/main.instructor') : trans('admin/main.organization')
        ];

        if ($status == 'reject') {
            sendNotification("become_instructor_request_rejected", $notifyOptions, $this->user_id);
        } else {
            sendNotification("become_instructor_request_approved", $notifyOptions, $this->user_id);
        }
    }
}
