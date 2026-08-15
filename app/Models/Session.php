<?php

namespace App\Models;

use App\Models\Traits\SequenceContent;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\CalendarLinks\Link;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
/**
 * @property int $number_row
 * @property int $present_count
 * @property int $late_count
 * @property int $total_students
 * @property int $absent_count
 * @property \App\Models\SessionAttendance|null $myAttendance
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\SessionAttendance[] $attendances
 * @property \Illuminate\Database\Eloquent\Collection|\App\User[] $participatesUsers
 */
class Session extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'api_secret' => 'encrypted',
        'moderator_secret' => 'encrypted',
    ];
    protected $table = 'sessions';
    protected $dateFormat = 'U';

    static $Active = 'active';
    static $Inactive = 'inactive';
    static $Status = ['active', 'inactive'];

    static $sessionApis = ['local', 'big_blue_button', 'zoom', 'agora', 'jitsi'];


    public $translatedAttributes = ['title', 'description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }


    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function sessionReminds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\SessionRemind', 'session_id', 'id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SessionAttendance::class, 'session_id', 'id');
    }

    public function attendanceNotification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SessionAttendanceNotification::class, 'session_id', 'id');
    }

    public function learningStatus(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\CourseLearning', 'session_id', 'id');
    }

    public function chapter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
    }

    public function agoraHistory(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('App\Models\AgoraHistory', 'session_id', 'id');
    }

    public function personalNote(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne('App\Models\CoursePersonalNote', 'targetable');
    }

    public function reserveMeeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ReserveMeeting::class, 'reserve_meeting_id', 'id');
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function meetingPackageSold(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MeetingPackageSold::class, 'meeting_package_sold_id', 'id');
    }

    public function addToCalendarLink()
    {
        try {
            $date = \DateTime::createFromFormat('j M Y H:i', dateTimeFormat($this->date, 'j M Y H:i', false));

            $link = Link::create($this->title, $date, $date); //->description('Cookies & cocktails!')

            return $link->google();
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function getJoinLink($zoom_start_link = false)
    {
        if ($zoom_start_link and auth()->check() and auth()->id() == $this->creator_id and $this->session_api == 'zoom') {
            return $this->zoom_start_link;
        }
        return "/panel/sessions/{$this->id}/join";
    }

    public function isFinished(): bool
    {
        $agoraHistory = $this->agoraHistory;

        $finished = (!empty($agoraHistory) and !empty($agoraHistory->end_at));

        if (!$finished) {
            $finished = (time() > (($this->duration * 60) + $this->date));
        }

        return $finished;
    }

    public function checkPassedItem()
    {
        $result = false;

        if (auth()->check()) {
            $check = $this->learningStatus()->where('user_id', auth()->id())->count();

            $result = ($check > 0);
        }

        return $result;
    }

    public function getSessionStreamType()
    {
        $setting = null;

        if (!empty($this->reserve_meeting_id)) {
            $setting = getFeaturesSettings('meeting_live_stream_type');
        } else {
            $setting = getFeaturesSettings('course_live_stream_type');
        }

        $sessionStreamType = 'single';

        if (!empty($setting) and in_array($setting, ['single', 'multiple'])) {
            $sessionStreamType = $setting;
        }

        return $sessionStreamType;
    }

    public function getUserAttendanceStatus($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $attendance = $this->attendances()->where('student_id', $user->id)->first();

        return !empty($attendance) ? $attendance->status : "absent";
    }
}
