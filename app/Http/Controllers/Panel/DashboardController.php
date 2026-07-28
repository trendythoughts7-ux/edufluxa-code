<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Comment;
use App\Models\Gift;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\Subscribe;
use App\Models\Support;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        $data = [
            'pageTitle' => trans('panel.dashboard'),
        ];

        if ($user->isUser()) {
            $data = array_merge($data, $this->getStudentDashboardData($request, $user));
        } else {
            $data = array_merge($data, $this->getInstructorDashboardData($request, $user));
        }

        // Upcoming Events
        $data = array_merge($data, $this->handleDashboardUpcomingEvents($user));

        // Gifts Modal
        $data['giftModal'] = $this->showGiftModal($user);


        $generalSettings = getGeneralSettings();
        $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];
        $data['isRtl'] = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));

        return view('design_1.panel.dashboard.index', $data);
    }

    private function getStudentDashboardData(Request $request, $user): array
    {
        $data = [];

        $data['activeSubscribe'] = Subscribe::getActiveSubscribe($user->id);
        $data['authUserBalanceCharge'] = $user->getAccountingCharge();
        $data['authUserReadyPayout'] = $user->getPayout();


        $userBoughtWebinarsIds = $user->getPurchasedCoursesIds();

        // hello_box
        $data['helloBox'] = app(\App\Services\App\DashboardDataService::class)->getStudentHelloBoxData($user, $userBoughtWebinarsIds);

        // Courses Overview
        $data['coursesOverview'] = app(\App\Services\App\DashboardDataService::class)->getStudentCoursesOverviewData($user, $userBoughtWebinarsIds);

        // My Assignments
        $data['myAssignments'] = app(\App\Services\App\DashboardDataService::class)->getStudentMyAssignmentsData($user, $userBoughtWebinarsIds);

        // Learning Activity
        $data['learningActivity'] = app(\App\Services\App\DashboardDataService::class)->getStudentLearningActivityData($user, $userBoughtWebinarsIds);

        // Noticeboard
        $data['unreadNoticeboards'] = $user->getUnreadNoticeboards();

        // Support Messages
        $data['supportMessages'] = app(\App\Services\App\DashboardDataService::class)->getStudentSupportMessagesData($user, $userBoughtWebinarsIds);

        // My quizzes
        $data['myQuizzes'] = app(\App\Services\App\DashboardDataService::class)->getStudentMyQuizzesData($user, $userBoughtWebinarsIds);

        // Upcoming Live Sessions
        $data['upcomingLiveSessions'] = app(\App\Services\App\DashboardDataService::class)->getStudentUpcomingLiveSessionsData($user, $userBoughtWebinarsIds);

        // Open Meetings
        $data['openMeetings'] = app(\App\Services\App\DashboardDataService::class)->getStudentOpenMeetingsData($user, $userBoughtWebinarsIds);

        return $data;
    }

    private function getInstructorDashboardData(Request $request, $user): array
    {
        $data = [];

        $userWebinars = Webinar::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('webinars.creator_id', $user->id);
                $query->orWhere('webinars.teacher_id', $user->id);
            })
            ->leftJoin('sales', function ($join) use ($user) {
                $join->on('sales.webinar_id', '=', 'webinars.id');
                $join->whereNull('sales.refund_at');
                //$join->where('sales.amount', '>', '0');
            })
            ->select('webinars.*',
                DB::raw('count(sales.webinar_id) as sales_count'),
                DB::raw('sum(sales.total_amount) as sales_amount')
            )
            ->groupBy('webinars.id')
            ->orderBy('sales_count', 'desc')
            ->get();

        $userWebinarsIds = $userWebinars->pluck('id')->toArray();

        $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');


        // hello_box
        $data['helloBox'] = app(\App\Services\App\DashboardDataService::class)->getInstructorHelloBoxData($user, $meetingIds, $userWebinars);

        // Courses Overview
        $data['coursesOverview'] = app(\App\Services\App\DashboardDataService::class)->getInstructorCoursesOverviewData($user, $userWebinars);

        // Sales Overview
        $data['salesOverview'] = app(\App\Services\App\DashboardDataService::class)->getInstructorSalesOverviewData($user, $userWebinarsIds);

        // Pending Student Assignments
        $data['pendingStudentAssignments'] = app(\App\Services\App\DashboardDataService::class)->getInstructorStudentAssignmentsData($user, $userWebinarsIds);

        // Registration Plan
        $userPackage = new UserPackage($user);
        $data['registrationPlan'] = $userPackage->getPackage();

        // Current Balance
        $data['authUserBalanceCharge'] = $user->getAccountingCharge();
        $data['authUserReadyPayout'] = $user->getPayout();

        // Noticeboard
        $data['unreadNoticeboards'] = $user->getUnreadNoticeboards();

        // Support Messages
        $data['supportMessages'] = app(\App\Services\App\DashboardDataService::class)->getInstructorSupportMessagesData($user, $userWebinarsIds);

        // Visitors Statistics
        $data['visitorsStatistics'] = app(\App\Services\App\DashboardDataService::class)->getInstructorVisitorsStatisticsData($user, $userWebinarsIds);

        if ($user->isTeacher()) {
            // Upcoming Live Sessions
            $data['upcomingLiveSessions'] = app(\App\Services\App\DashboardDataService::class)->getInstructorUpcomingLiveSessionsData($user, $userWebinarsIds);

            // Review Student Quizzes
            $data['reviewStudentQuizzes'] = app(\App\Services\App\DashboardDataService::class)->getInstructorReviewStudentQuizzes($user, $userWebinarsIds);

            // Open Meetings
            $data['openMeetings'] = app(\App\Services\App\DashboardDataService::class)->getInstructorOpenMeetingsData($user, $userWebinarsIds);

        } else { // Organization
            // Top Instructors
            $data['topInstructors'] = app(\App\Services\App\DashboardDataService::class)->getOrganizationTopInstructorsData($user);

            // Top Students
            $data['topStudents'] = app(\App\Services\App\DashboardDataService::class)->getOrganizationTopStudentsData($user);
        }


        return $data;
    }

    private function handleDashboardUpcomingEvents($user)
    {
        $eventsController = (new EventsCalendarController());
        $eventsController->user = $user;
        $eventsController->userBoughtWebinarsIds = $user->getPurchasedCoursesIds();

        $eventsWithTimestamp = $eventsController->getAllEventsReturnWithTimestamp();
        $getUpcomingEvents = $eventsController->getUpcomingEvents(2);
        $upcomingEvents = $getUpcomingEvents['upcomingEvents'];
        $totalEvents = $getUpcomingEvents['total'];

        return [
            'upcomingEvents' => $upcomingEvents,
            'totalEvents' => $totalEvents,
            'eventsWithTimestamp' => $eventsWithTimestamp,
        ];
    }

    private function showGiftModal($user)
    {
        $gift = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->where('viewed', false)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->first();

        if (!empty($gift)) {
            $gift->update([
                'viewed' => true
            ]);

            $data = [
                'gift' => $gift
            ];

            $result = view()->make('design_1.web.gift.modal.show_to_receipt', $data)->render();
            $result = str_replace(array("\r\n", "\n", "  "), '', $result);

            return $result;
        }

        return null;
    }

}
