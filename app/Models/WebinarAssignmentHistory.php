<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarAssignmentHistory extends Model
{
    protected $table = 'webinar_assignment_history';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $assignmentHistoryStatus = ['pending', 'passed', 'not_passed', 'not_submitted'];
    static $pending = 'pending';
    static $passed = 'passed';
    static $notPassed = 'not_passed';
    static $notSubmitted = 'not_submitted';

    public function instructor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'instructor_id', 'id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\User', 'student_id', 'id');
    }

    public function assignment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\WebinarAssignment', 'assignment_id', 'id');
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\WebinarAssignmentHistoryMessage', 'assignment_history_id', 'id');
    }
}
