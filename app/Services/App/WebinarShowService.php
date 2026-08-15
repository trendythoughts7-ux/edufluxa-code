<?php

namespace App\Services\App;

use App\Models\Webinar;
use App\Models\WebinarChapter;
use Illuminate\Support\Facades\DB;
use App\Mixins\Installment\InstallmentPlans;
use App\Mixins\Cashback\CashbackRules;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AdvertisingBanner;

class WebinarShowService
{
    public function getCourseWithFullRelations($slug, $user)
    {
        return Webinar::where('slug', $slug)
            ->with([
                'quizzes' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['quizResults', 'quizQuestions']);
                },
                'tags',
                'prerequisites' => function ($query) {
                    $query->with(['course' => function ($query) {
                        $query->with(['teacher' => function ($qu) {
                            $qu->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                        }]);
                    }]);
                    $query->orderBy('order', 'asc');
                },
                'relatedCourses' => function ($query) {
                    $query->whereHas('course', function ($query) {
                        $query->where('status', 'active');
                    })->with(['course.teacher', 'course.category', 'course.reviews' => function ($query) {
                        $query->where('status', 'active');
                    }]);
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'webinarExtraDescription' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'chapters' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive);
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }
                    ]);
                },
                'files' => function ($query) use ($user) {
                    $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                        ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                        ->where('files.status', WebinarChapter::$chapterActive)
                        ->orderBy('chapterOrder', 'asc')
                        ->orderBy('files.order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'textLessons' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->withCount(['attachments'])
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'sessions' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'assignments' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive);
                },
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'filterOptions',
                'category',
                'teacher',
                'reviews' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                },
                'noticeboards'
            ])
            //->where('status', 'active')
            ->first();
    }

    public function checkCourseActiveAccessGate($course, $user)
    {
        if ($course->status != "active" and (empty($user) or (!$user->isAdmin() and !$course->canAccess($user)))) {
            $data = [
                'pageTitle' => trans('update.access_denied'),
                'pageRobot' => getPageRobotNoIndex(),
            ];
            return view('design_1.web.courses.not_access.index', $data);
        }

        return "ok";
    }

    public function checkCourseStudentOnlyAccessGate($course, $user)
    {
        if ($course->only_for_students and (empty($user) or (!$user->isAdmin() and !$course->canAccess($user)))) {
            $data = [
                'pageTitle' => trans('update.access_denied'),
                'pageRobot' => getPageRobotNoIndex(),
            ];
            return view('design_1.web.courses.not_access.index', $data);
        }

        return "ok";
    }

    public function resolveCourseBoughtAndPrivacyStatus($course, $user)
    {
        $hasBought = $course->checkUserHasBought($user, true, true);
        $isPrivate = $course->private;

        if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin())) {
            $isPrivate = false;
        }

        if ($isPrivate and $hasBought) {
            $isPrivate = false;
        }

        return [
            'hasBought' => $hasBought,
            'isPrivate' => $isPrivate,
        ];
    }

    /**
     * @param \App\Models\Webinar $course
     */
    public function resolveCourseCommercePricingContext($course, $user, $canSale, $showInstallments)
    {
        $installments = null;
        $cashbackRules = null;
        $instructorDiscounts = null;

        if ($canSale and !empty($course->price) and $course->price > 0 and $showInstallments and getInstallmentsSettings("status") and (empty($user) or $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans("courses", $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        if ($canSale and !empty($course->price) and getFeaturesSettings("cashback_active") and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $cashbackRules = $cashbackRulesMixin->getRules("courses", $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        if (!empty(getFeaturesSettings("frontend_coupons_status"))) {
            $instructorDiscounts = Discount::query()
                ->where(function (Builder $query) use ($course) {
                    $query->where("creator_id", $course->creator_id);
                    $query->orWhere("creator_id", $course->teacher_id);
                })
                ->where(function (Builder $query) use ($course) {
                    $query->where("source", "all");
                    $query->orWhere(function (Builder $query) use ($course) {
                        $query->where("source", Discount::$discountSourceCourse);
                        $query->where(function (Builder $query) use ($course) {
                            $query->whereHas("discountCourses", function ($query) use ($course) {
                                $query->where("course_id", $course->id);
                            });
                            $query->whereDoesntHave("discountCourses");
                        });
                    });
                })
                ->where("status", "active")
                ->where("expired_at", ">", time())
                ->get();
        }

        return [
            "installments" => $installments,
            "cashbackRules" => $cashbackRules,
            "instructorDiscounts" => $instructorDiscounts,
        ];
    }

    public function resolveCourseContentDisplayContext($course)
    {
        $webinarContentCount = 0;
        if (!empty($course->sessions)) {
            $webinarContentCount += $course->sessions->count();
        }
        if (!empty($course->files)) {
            $webinarContentCount += $course->files->count();
        }
        if (!empty($course->textLessons)) {
            $webinarContentCount += $course->textLessons->count();
        }
        if (!empty($course->quizzes)) {
            $webinarContentCount += $course->quizzes->count();
        }
        if (!empty($course->assignments)) {
            $webinarContentCount += $course->assignments->count();
        }

        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['course', 'course_sidebar'])
            ->get();

        $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

        $filesWithoutChapter = $course->files->whereNull('chapter_id');

        $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

        $quizzes = $course->quizzes->whereNull('chapter_id');

        return [
            'webinarContentCount' => $webinarContentCount,
            'advertisingBanners' => $advertisingBanners,
            'sessionsWithoutChapter' => $sessionsWithoutChapter,
            'filesWithoutChapter' => $filesWithoutChapter,
            'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
            'quizzes' => $quizzes,
        ];
    }
}
