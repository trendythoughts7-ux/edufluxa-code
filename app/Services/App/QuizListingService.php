<?php

namespace App\Services\App;

use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\QuizzesQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizListingService
{
    /**
     * Single entry point for the quiz listing page.
     * Returns a structured array with an 'action' key ('ajax' or 'view')
     * so the controller decides the actual HTTP-layer response
     * (pagination object building via base Controller::makePagination()
     * stays in the controller since it's not accessible from a service).
     */
    public function getIndexData(Request $request, $user, $perPage)
    {
        $query = Quiz::where('quizzes.creator_id', $user->id);
        $query = $this->applyFilters($request, $query);

        $page = $request->get('page') ?? 1;
        $count = $perPage;

        $cloneQuery = deepClone($query);
        $total = DB::table(DB::raw("({$cloneQuery->toSql()}) as sub"))
            ->mergeBindings($cloneQuery->getQuery()) // bind parameters
            ->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $quizzes = $query
            ->with([
                'webinar',
                'quizQuestions',
                'quizResults',
            ])
            ->get();

        foreach ($quizzes as $quiz) {
            $countSuccess = $quiz->quizResults
                ->where('status', \App\Models\QuizzesResult::$passed)
                ->pluck('user_id')
                ->count();

            $rate = 0;
            if ($countSuccess) {
                $rate = round($countSuccess / $quiz->quizResults->count() * 100);
            }

            $quiz->userSuccessRate = $rate;
        }

        if ($request->ajax()) {
            return [
                'action' => 'ajax',
                'quizzes' => $quizzes,
                'total' => $total,
                'count' => $count,
            ];
        }

        $topStats = $this->getTopStats($user);

        $allQuizzesLists = Quiz::select('id', 'webinar_id')
            ->where('creator_id', $user->id)
            ->where('status', 'active')
            ->get();

        return [
            'action' => 'view',
            'quizzes' => $quizzes,
            'total' => $total,
            'count' => $count,
            'topStats' => $topStats,
            'allQuizzesLists' => $allQuizzesLists,
        ];
    }

    private function getTopStats($user)
    {
        $query = Quiz::where('creator_id', $user->id);
        $quizIds = $query->pluck('id')->toArray();

        $quizzesCount = deepClone($query)->count();
        $questionsCount = QuizzesQuestion::query()->whereIn('quiz_id', $quizIds)->count();
        $userCount = QuizzesResult::query()->whereIn('quiz_id', $quizIds)->count();


        return [
            'quizzesCount' => $quizzesCount,
            'questionsCount' => $questionsCount,
            'userCount' => $userCount,
        ];
    }

    private function applyFilters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $quiz_id = $request->get('quiz_id');
        $total_mark = $request->get('total_mark');
        $status = $request->get('status');
        $active_quizzes = $request->get('active_quizzes');
        $questions_type = $request->get('questions_type');
        $sort = $request->get('sort');


        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($quiz_id) and $quiz_id != 'all') {
            $query->where('id', $quiz_id);
        }

        if ($status and $status !== 'all') {
            $query->where('status', strtolower($status));
        }

        if ($questions_type and $questions_type !== 'all') {
            if ($questions_type == "multiple") {
                $query->whereHas('quizQuestions', function ($query) {
                    $query->where('type', 'multiple');
                });
            } else if ($questions_type == "descriptive") {
                $query->whereHas('quizQuestions', function ($query) {
                    $query->where('type', 'descriptive');
                });
            }
        }

        if (!empty($active_quizzes)) {
            $query->where('status', 'active');
        }

        if ($total_mark) {
            $query->where('total_mark', '>=', $total_mark);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'questions_asc':
                    $query->join('quizzes_questions', 'quizzes_questions.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_questions.quiz_id', DB::raw('count(quizzes_questions.quiz_id) as questions_count'))
                        ->groupBy('quizzes_questions.quiz_id')
                        ->orderBy('questions_count', 'asc');
                    break;
                case 'questions_desc':
                    $query->join('quizzes_questions', 'quizzes_questions.quiz_id', '=', 'quizzes.id')
                        ->select('quizzes.*', 'quizzes_questions.quiz_id', DB::raw('count(quizzes_questions.quiz_id) as questions_count'))
                        ->groupBy('quizzes_questions.quiz_id')
                        ->orderBy('questions_count', 'desc');
                    break;
                case 'time_asc':
                    $query->orderBy('time', 'asc');
                    break;
                case 'time_desc':
                    $query->orderBy('time', 'desc');
                    break;
                case 'pass_mark_asc':
                    $query->orderBy('pass_mark', 'asc');
                    break;
                case 'pass_mark_desc':
                    $query->orderBy('pass_mark', 'desc');
                    break;
                case 'create_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'create_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}