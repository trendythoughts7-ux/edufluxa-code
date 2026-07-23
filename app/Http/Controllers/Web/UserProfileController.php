<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Discount;
use App\Models\ForumTopic;
use App\Models\MeetingPackage;
use App\Models\Newsletter;
use App\Models\Product;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\UserOccupation;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use App\Models\Follow;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use LaravelIdea\Helper\App\Models\_IH_Webinar_QB;

class UserProfileController extends Controller
{

    public function profile(Request $request, $username)
    {
        $user = User::where('username', $username)
            ->with([
                'userMetas'
            ])
            ->first();

        if (!$user) {
            abort(404);
        }

        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        // Safe Badges Check
        try {
            $userBadges = $user->getBadges();
        } catch (\Exception $e) {
            $userBadges = collect();
        }

        // Safe Meeting Check
        try {
            $meeting = Meeting::query()->select('*', DB::raw('LEAST(online_group_amount, in_person_group_amount) as min_group_amount'))
                ->where('creator_id', $user->id)
                ->with([
                    'meetingTimes'
                ])
                ->first();
        } catch (\Exception $e) {
            $meeting = null;
        }

        $times = [];
        $installments = null;
        $cashbackRules = null;
        $nearestDayDate = null;

        if (!empty($meeting) and !empty($meeting->meetingTimes)) {
            $timeDayLabels = $meeting->meetingTimes->groupby('day_label')->toArray();
            $times = convertDayToNumber($timeDayLabels);
            $nearestDayDate = $this->getNearestDate($meeting->meetingTimes);

            $authUser = auth()->user();

            /* Cashback Rules - Safe Wrapper */
            if (function_exists('getFeaturesSettings') && getFeaturesSettings('cashback_active') && (empty($authUser) || !$authUser->disable_cashback)) {
                try {
                    if (class_exists('App\Mixins\Cashback\CashbackRules')) {
                        $cashbackRulesMixin = new CashbackRules($authUser);
                        $cashbackRules = $cashbackRulesMixin->getRules('meetings', null, null, null, $user->id);
                    }
                } catch (\Exception $e) {
                    $cashbackRules = null;
                }
            }
        }

        $followings = $user->following();
        $followers = $user->followers();

        $authUserIsFollower = false;
        if (auth()->check()) {
            $authUserIsFollower = $followers->where('follower', auth()->id())
                ->where('status', Follow::$accepted)
                ->first();
        }

        $userMetas = $user->userMetas;
        $occupations = $user->occupations()
            ->with([
                'category'
            ])->get();


        $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
        $appointments = ReserveMeeting::whereIn('meeting_id', $meetingIds)
            ->whereNotNull('reserved_at')
            ->where('status', '!=', ReserveMeeting::$canceled)
            ->count();

        $instructorDiscounts = null;

        if (!empty(getFeaturesSettings('frontend_coupons_status'))) {
            $instructorDiscounts = Discount::query()
                ->where('creator_id', $user->id)
                ->where(function (Builder $query) {
                    $query->where('source', 'all');
                    $query->orWhere('source', Discount::$discountSourceMeeting);
                })
                ->where('status', 'active')
                ->where('expired_at', '>', time())
                ->get();
        }

        $coursesCount = $this->getUserCoursesQuery($user->id)->count();

        $data = [
            'pageTitle' => $user->full_name . ' ' . trans('public.profile'),
            'user' => $user,
            'userBadges' => $userBadges,
            'meeting' => $meeting,
            'times' => $times,
            'nearestDayDate' => $nearestDayDate,
            'userRates' => $user->rates(true),
            'userFollowers' => $followers,
            'userFollowing' => $followings,
            'authUserIsFollower' => $authUserIsFollower,
            'educations' => $userMetas->where('name', 'education'),
            'experiences' => $userMetas->where('name', 'experience'),
            'occupations' => $occupations,
            'coursesCount' => $coursesCount,
            'appointments' => $appointments,
            'meetingTimezone' => $meeting ? $meeting->getTimezone() : null,
            'cashbackRules' => $cashbackRules,
            'instructorDiscounts' => $instructorDiscounts
        ];

        // Courses
        $data = array_merge($data, $this->getUserCourses($request, $username, $user));

        // Products
        if (!empty(getStoreSettings('status')) and getStoreSettings('status')) {
            $data = array_merge($data, $this->getUserProducts($request, $username, $user));
        }

        // Posts
        $data = array_merge($data, $this->getUserPosts($request, $username, $user));

        // Forum Topic
        $data = array_merge($data, $this->getUserForumTopics($request, $username, $user));

        // Instructors
        if ($user->isOrganization()) {
            $data = array_merge($data, $this->getOrganizationInstructors($request, $username, $user));
        }

        if (!empty(getMeetingPackagesSettings("status")) and !empty($meeting) and $meeting->enable_meeting_packages) {
            $data['userMeetingPackages'] = MeetingPackage::query()->where('creator_id', $user->id)
                ->where('enable', true)
                ->get();
        }

        return view('design_1.web.users.profile.index', $data);
    }

    public function getNearestDate($meetingTimes)
    {
        $date = null;

        if (!empty($meetingTimes) and count($meetingTimes)) {
            $timeDayLabels = $meetingTimes->groupby('day_label')->toArray();
            $days = array_keys($timeDayLabels);

            $currentDay = strtotime('yesterday');
            $nextDays = [];
            foreach ($days as $day) {
                $nextDays[$day] = strtotime("next $day", $currentDay);
            }

            $nearestTimestamp = min($nextDays);
            $nearestDay = array_search($nearestTimestamp, $nextDays);

            $nearestDayMeetingTimes = $meetingTimes->where('day_label', $nearestDay);

            $minTime = null;
            $selectedTime = null;

            foreach ($nearestDayMeetingTimes as $nearestDayMeetingTime) {
                $explodetime = explode('-', $nearestDayMeetingTime->time);
                $timeHoursTimestamp = strtotime($explodetime[0]);

                if (empty($minTime) or $minTime > $timeHoursTimestamp) {
                    $minTime = $timeHoursTimestamp;
                    $selectedTime = $nearestDayMeetingTime;
                }
            }

            if (!empty($nextDays[$nearestDay])) {
                $date = dateTimeFormat($nextDays[$nearestDay], 'j M Y');
            }

            if (!empty($selectedTime)) {
                $date .= " " . $selectedTime->time;
            }

        }

        return $date;
    }

    private function getUserCoursesQuery($userId)
    {
        return Webinar::query()->where('status', Webinar::$active)
            ->where('private', false)
            ->where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                    ->orWhere('teacher_id', $userId);
            });
    }

    public function getUserCourses(Request $request, $username, $user = null)
    {
        if (empty($user)) {
            $user = User::query()->where('username', $username)->first();
        }

        if (!empty($user)) {
            $count = 6;
            $page = $request->get('page') ?? 1;

            $query = $this->getUserCoursesQuery($user->id);

            $total = $query->count();

            $query->limit($count);
            $query->offset(($page - 1) * $count);

            $courses = $query
                ->with([
                    'teacher' => function ($qu) {
                        $qu->select('id', 'full_name', 'username', 'bio', 'avatar', 'avatar_settings');
                    },
                    'reviews',
                    'tickets',
                    'feature'
                ])
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($request->ajax()) {
                $html = (string)view()->make('design_1.web.courses.components.cards.grids.index', ['courses' => $courses, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16", 'withoutStyles' => true]);

                return response()->json([
                    'data' => $html,
                    'has_more_item' => (($page * $count) < $total),
                ]);
            }

            return [
                'courses' => $courses,
                'hasMoreCourses' => (($page * $count) < $total),
            ];
        }

        return [
            'courses' => collect(),
            'hasMoreCourses' => false,
        ];
    }

    public function followToggle($username)
    {
        $authUser = auth()->user();
        $user = User::where('username', $username)->first();

        $followStatus = false;
        $follow = Follow::where('follower', $authUser->id)
            ->where('user_id', $user->id)
            ->first();

        if (empty($follow)) {
            Follow::create([
                'follower' => $authUser->id,
                'user_id' => $user->id,
                'status' => Follow::$accepted,
            ]);

            $followStatus = true;
        } else {
            $follow->delete();
        }

        return response()->json([
            'code' => 200,
            'follow' => $followStatus,
            'followers' => $user->followers()->count(),
        ], 200);
    }

    public function availableTimes(Request $request, $username)
    {
        $timestamp = $request->get('timestamp');
        $dayLabel = $request->get('day_label');
        $date = $request->get('date');

        $user = User::where('username', $username)
            ->whereIn('role_name', [Role::$teacher, Role::$organization])
            ->where('status', 'active')
            ->first();

        if (!$user) {
            abort(404);
        }

        $meeting = Meeting::where('creator_id', $user->id)
            ->with(['meetingTimes'])
            ->first();

        $resultMeetingTimes = [];

        if (!empty($meeting->meetingTimes)) {

            if (empty($dayLabel)) {
                $dayLabel = dateTimeFormat($timestamp, 'l', false, false);
            }

            $dayLabel = mb_strtolower($dayLabel);

            $meetingTimes = $meeting->meetingTimes()->where('day_label', $dayLabel)->get();

            if (!empty($meetingTimes) and count($meetingTimes)) {

                foreach ($meetingTimes as $meetingTime) {
                    $can_reserve = true;

                    $reserveMeeting = ReserveMeeting::where('meeting_time_id', $meetingTime->id)
                        ->where('day', $date)
                        ->whereIn('status', ['pending', 'open'])
                        ->first();

                    if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                        $can_reserve = false;
                    }

                    $resultMeetingTimes[] = [
                        "id" => $meetingTime->id,
                        "time" => $meetingTime->time,
                        "description" => $meetingTime->description,
                        "can_reserve" => $can_reserve,
                        'meeting_type' => $meetingTime->meeting_type
                    ];
                }
            }
        }

        return response()->json([
            'times' => $resultMeetingTimes
        ], 200);
    }

    public function getUserProducts(Request $request, $username, $user = null)
    {
        if (empty($user)) {
            $user = User::query()->where('username', $username)->first();
        }

        if (!empty($user)) {
            $count = 6;
            $page = $request->get('page') ?? 1;

            $query = Product::query()->where('creator_id', $user->id)
                ->where('status', Product::$active);

            $total = $query->count();

            $query->limit($count);
            $query->offset(($page - 1) * $count);

            $products = $query
                ->with([
                    'creator' => function ($qu) {
                        $qu->select('id', 'full_name', 'username', 'bio', 'avatar', 'avatar_settings');
                    },
                    'category',
                    'reviews',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->ajax()) {
                $html = (string)view()->make('design_1.web.products.components.cards.grids.index', ['products' => $products, 'gridCardClassName' => "col-12 col-lg-6 mt-16", 'withoutStyles' => true]);

                return response()->json([
                    'data' => $html,
                    'has_more_item' => (($page * $count) < $total),
                ]);
            }

            return [
                'products' => $products,
                'hasMoreProducts' => (($page * $count) < $total),
            ];
        }

        return [
            'products' => collect(),
            'hasMoreProducts' => false,
        ];
    }

    public function getUserPosts(Request $request, $username, $user = null)
    {
        if (empty($user)) {
            $user = User::query()->where('username', $username)->first();
        }

        if (!empty($user)) {
            $count = 6;
            $page = $request->get('page') ?? 1;

            $query = Blog::query()->where('author_id', $user->id)
                ->where('status', 'publish');

            $total = $query->count();

            $query->limit($count);
            $query->offset(($page - 1) * $count);

            $posts = $query
                ->with([
                    'author' => function ($qu) {
                        $qu->select('id', 'full_name', 'username', 'bio', 'avatar', 'avatar_settings');
                    }
                ])
                ->withCount([
                    'comments' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->ajax()) {
                $html = (string)view()->make('design_1.web.blog.components.cards.grids.index', ['posts' => $posts, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16", 'withoutStyles' => true]);

                return response()->json([
                    'data' => $html,
                    'has_more_item' => (($page * $count) < $total),
                ]);
            }

            return [
                'posts' => $posts,
                'hasMorePosts' => (($page * $count) < $total),
            ];
        }

        return [
            'posts' => collect(),
            'hasMorePosts' => false,
        ];
    }

    public function getOrganizationInstructors(Request $request, $username, $user = null)
    {
        if (empty($user)) {
            $user = User::query()->where('username', $username)->first();
        }

        if (!empty($user)) {
            $count = 9;
            $page = $request->get('page') ?? 1;

            $query = User::where('organ_id', $user->id)
                ->where('role_name', Role::$teacher)
                ->where('status', 'active');

            $total = $query->count();

            $query->limit($count);
            $query->offset(($page - 1) * $count);

            $instructors = $query
                ->withCount([
                    'webinars'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->ajax()) {
                $html = '';

                foreach ($instructors as $instructor) {
                    $html .= (string)view()->make('design_1.web.users.profile.tabs.components.instructor', ['instructor' => $instructor]);
                }

                return response()->json([
                    'data' => $html,
                    'has_more_item' => (($page * $count) < $total),
                ]);
            }

            return [
                'instructors' => $instructors,
                'hasMoreInstructors' => (($page * $count) < $total),
            ];
        }

        return [
            'instructors' => collect(),
            'hasMoreInstructors' => false,
        ];
    }
    
public function getUserForumTopics(Request $request, $username, $user = null)
    {
        if (empty($user)) {
            $user = User::query()->where('username', $username)->first();
        }

        if (!empty($user)) {
            $count = 6;
            $page = $request->get('page') ?? 1;

            $query = ForumTopic::query()->where('creator_id', $user->id);

            $total = $query->count();

            $query->limit($count);
            $query->offset(($page - 1) * $count);

            $topics = $query->orderBy('created_at', 'desc')->get();

            if ($request->ajax()) {
                $html = (string)view()->make('design_1.web.users.profile.tabs.components.forum_topic', ['topics' => $topics]);

                return response()->json([
                    'data' => $html,
                    'has_more_item' => (($page * $count) < $total),
                ]);
            }

            return [
                'forumTopics' => $topics,
                'hasMoreForumTopics' => (($page * $count) < $total),
            ];
        }

        return [
            'forumTopics' => collect(),
            'hasMoreForumTopics' => false,
        ];
    }
}