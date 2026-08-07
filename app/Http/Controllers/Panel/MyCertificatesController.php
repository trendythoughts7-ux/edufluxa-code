<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MyCertificatesController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        $source = $request->get('source', 'quiz');
        $query = $this->getQueryBySource($user, $source);

        //$copyQuery = deepClone($query);
        $query = $this->handleFilters($request, deepClone($query));

        $pageListData = $this->getPageListData($request, $query, $user, $source);

        if ($request->ajax()) {
            return $pageListData;
        }

        $topStats = $this->handleTopStats($user);
        $pendingCertificates = $this->getPendingCertificates($user);

        $breadcrumbs = [
            ['text' => trans('update.platform'), 'url' => '/'],
            ['text' => trans('panel.dashboard'), 'url' => '/panel'],
            ['text' => trans('panel.certificates'), 'url' => null],
        ];

        $data = [
            'pageTitle' => trans('update.my_achievements'),
            'breadcrumbs' => $breadcrumbs,
            'pendingCertificates' => $pendingCertificates,
            'userHaveCertificatesItems' => $this->checkUserHaveCertificatesItems($user),
            ...$topStats,
            ...$pageListData,
        ];

        return view('design_1.panel.certificates.my_achievements.index', $data);
    }

    private function checkUserHaveCertificatesItems($user)
    {
        $query = $this->getQueryBySource($user, 'quiz');
        $certificatesItems = $query->count();

        if ($certificatesItems < 1) {
            $query2 = $this->getQueryBySource($user, 'completion');
            $certificatesItems = $query2->count();
        }

        return ($certificatesItems > 0);
    }

    private function getQueryBySource($user, $source): Builder
    {
        if ($source == 'quiz') {
            $query = Quiz::query()->where('status', Quiz::ACTIVE)
                ->whereHas('quizResults', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                    $query->where('status', QuizzesResult::$passed);
                });
        } else {
            $webinarsIds = $user->getPurchasedCoursesIds();

            $query = Webinar::query()->where('status', 'active')
                ->where('certificate', true)
                ->whereIn('id', $webinarsIds);
        }

        return $query;
    }

    private function getPendingCertificates($user)
    {
        $webinarsIds = $user->getPurchasedCoursesIds();
        $pendingQuizzes = collect();

        if (count($webinarsIds)) {
            $pendingQuizzes = Quiz::query()
                ->whereIn('webinar_id', $webinarsIds)
                ->where('certificate', true)
                ->where('status', Quiz::ACTIVE)
                ->whereDoesntHave('quizResults', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('webinar')
                ->orderBy('created_at', 'desc')
                ->get();

            // Performance: pre-fetch primary sales in one query instead of N queries inside the loop
            $preFetchedCertSales = \App\Models\Sale::query()
                ->where('buyer_id', $user->id)
                ->whereIn('webinar_id', $pendingQuizzes->pluck('webinar_id')->unique())
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->where('access_to_purchased_item', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->keyBy('webinar_id');

            foreach ($pendingQuizzes as $qKey => $pendingQuiz) {
                $pendingQuiz->type = "quiz";
                $pendingQuiz->expiry_timestamp = null;

                if (!empty($pendingQuiz->expiry_days)) {
                    $webinar = $pendingQuiz->webinar;

                    if (!empty($webinar)) {
                        $sale = $preFetchedCertSales->get($webinar->id) ?? $webinar->getSaleItem($user);

                        if (!empty($sale)) {
                            $purchaseDate = $sale->created_at;
                            $gift = $sale->gift;

                            if (!empty($gift) and !empty($gift->date)) {
                                $purchaseDate = $gift->date;
                            }

                            $pendingQuiz->expiry_timestamp = strtotime("+{$pendingQuiz->expiry_days} days", $purchaseDate);
                        }
                    }
                }
            }
        }

        $pendingWebinars = Webinar::where('status', 'active')
            ->where('certificate', true)
            ->whereHas('sales', function ($query) use ($user) {
                $query->where('buyer_id', $user->id);
                $query->whereNull('refund_at');
                $query->where('access_to_purchased_item', true);
            })
            ->whereDoesntHave('certificates', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->get();

        if ($pendingQuizzes->isNotEmpty()) {
            $pendingQuizzes = $pendingQuizzes->sortByDesc('expiry_timestamp');
        }

        return $pendingWebinars->merge($pendingQuizzes);
    }

    private function handleTopStats($user): array
    {
        $quizQuery = Quiz::query()->where('status', Quiz::ACTIVE)
            ->whereHas('quizResults', function ($query) use ($user) {
                $query->where('user_id', $user->id);
                $query->where('status', QuizzesResult::$passed);
            });

        $webinarsIds = $user->getPurchasedCoursesIds();

        $webinarQuery = Webinar::query()->where('status', 'active')
            ->where('certificate', true)
            ->whereIn('id', $webinarsIds);


        $quizCertificatesCount = deepClone($quizQuery)->count();
        $completionCertificatesCount = deepClone($webinarQuery)->count();

        $totalCertificatesCount = $quizCertificatesCount + $completionCertificatesCount;

        return [
            'totalCertificatesCount' => $totalCertificatesCount,
            'quizCertificatesCount' => $quizCertificatesCount,
            'completionCertificatesCount' => $completionCertificatesCount,
        ];
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {

        return $query;
    }

    private function getPageListData(Request $request, Builder $query, $user, $source = "quiz")
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        if ($source == 'quiz') {
            $certificatesItems = $query
                ->with([
                    'webinar',
                    'certificates' => function ($query) use ($user) {
                        $query->where('student_id', $user->id);
                        $query->orderBy('created_at', 'desc');
                    },
                    'quizResults' => function ($query) use ($user) {
                        $query->where('status', QuizzesResult::$passed);
                        $query->where('user_id', $user->id);
                        $query->orderBy('created_at', 'desc');
                    },
                ])
                ->get();

            foreach ($certificatesItems as $quiz) {
                /** @var \App\Models\Quiz $quiz */
                $userLastQuizResult = $quiz->quizResults->first();
                $canDownloadCertificate = (!empty($userLastQuizResult) and $userLastQuizResult->status == QuizzesResult::$passed);

                $lastCertificate = $quiz->certificates->first();

                if ($canDownloadCertificate) {
                    $lastCertificate = $quiz->getUserCertificate($user, $userLastQuizResult);
                }

                $quiz->userLastResult = $userLastQuizResult;
                $quiz->lastCertificate = $lastCertificate;
                $quiz->can_download_certificate = $canDownloadCertificate;
            }

        } else {
            $certificatesItems = $query
                ->get();

            foreach ($certificatesItems as $course) {
                /** @var \App\Models\Webinar $course */
                $course->lastCertificate = $course->makeCertificateForUser($user);
            }
        }

        if ($request->ajax()) {
            return $this->handleAjaxResponse($request, $certificatesItems, $total, $count, $source);
        }

        return [
            'certificatesItems' => $certificatesItems,
            'pagination' => $this->makePagination($request, $certificatesItems, $total, $count, true),
        ];
    }

    private function handleAjaxResponse(Request $request, $certificatesItems, $total, $count, $source)
    {
        $html = "";

        foreach ($certificatesItems as $certificateItem) {
            if ($source == 'quiz') {
                $html .= view()->make('design_1.panel.certificates.my_achievements.quiz_item_table', ['quizItem' => $certificateItem])->render();
            } else {
                $html .= view()->make('design_1.panel.certificates.my_achievements.course_item_table', ['course' => $certificateItem])->render();
            }
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $certificatesItems, $total, $count, true)
        ]);
    }

    public function download($certificateId)
    {
        $user = auth()->user();
        $certificate = Certificate::query()->where('id', $certificateId)
            ->where('student_id', $user->id)
            ->first();

        if (!empty($certificate)) {
            $makeCertificate = new MakeCertificate();
            return $makeCertificate->showCertificateByType($certificate);
        }

        abort(403);
    }

}
