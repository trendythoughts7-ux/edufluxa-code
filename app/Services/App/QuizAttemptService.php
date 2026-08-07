<?php

namespace App\Services\App;

use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\Reward;
use App\Models\RewardAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizAttemptService
{
    public function getOverviewData(Request $request, $id, $user)
    {
        $quiz = Quiz::where('id', $id)
            ->where('status', 'active')
            ->with([
                'creator',
                'webinar'
            ])
            ->first();
        if (empty($quiz)) {
            return ['action' => 'not_found'];
        }
        if (!empty($quiz->webinar_id)) {
            $webinar = $quiz->webinar;
            $checkUserHasBought = $webinar->checkUserHasBought($user);
            if (!$checkUserHasBought) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('cart.you_not_purchased_this_course'),
                    'status' => 'error'
                ];
                return ['action' => 'no_access', 'toast' => $toastData];
            }
        }
        $data = [
            'pageTitle' => trans('update.quiz_overview'),
            'quiz' => $quiz,
            'webinar' => $webinar,
        ];
        $data = array_merge($data, $this->getQuizStats($quiz, $user));
        return ['action' => 'success', 'data' => $data];
    }

    public function getStartData(Request $request, $id, $user)
    {
        $quiz = Quiz::where('id', $id)->first();
        if ($quiz) {
            if (!empty($quiz->webinar_id)) {
                $webinar = $quiz->webinar;
                $checkUserHasBought = $webinar->checkUserHasBought($user);
                if (!$checkUserHasBought) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('cart.you_not_purchased_this_course'),
                        'status' => 'error'
                    ];
                    return ['action' => 'no_access', 'toast' => $toastData];
                }
                if (!empty($quiz->expiry_days)) {
                    $hasAccess = $quiz->checkCanAccessByExpireDays($user);
                    if (!$hasAccess) {
                        $toastData = [
                            'title' => trans('public.request_failed'),
                            'msg' => trans('update.your_access_to_this_quiz_has_been_expired'),
                            'status' => 'error'
                        ];
                        return ['action' => 'expired', 'toast' => $toastData];
                    }
                }
            }
            $checkUserCanStartByAttempt = $quiz->checkUserCanStartByAttempt($user);
            if ($checkUserCanStartByAttempt) {
                $newQuizStart = QuizzesResult::create([
                    'quiz_id' => $quiz->id,
                    'user_id' => $user->id,
                    'results' => '',
                    'user_grade' => 0,
                    'status' => 'waiting',
                    'created_at' => time()
                ]);
                $data = [
                    'pageTitle' => trans('quiz.quiz_start'),
                    'quiz' => $quiz,
                    'webinar' => $quiz->webinar,
                    'newQuizStart' => $newQuizStart,
                ];
                $data = array_merge($data, $this->getQuizStats($quiz, $user, true));
                return ['action' => 'success', 'data' => $data];
            } else {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('quiz.cant_start_quiz'),
                    'status' => 'error'
                ];
                return ['action' => 'cant_start', 'toast' => $toastData];
            }
        }
        return ['action' => 'not_found'];
    }

    public function storeResult(Request $request, $id, $user)
    {
        $quiz = Quiz::where('id', $id)->first();

        if ($quiz) {
            $results = $request->get('question');
            $quizResultId = $request->get('quiz_result_id');

            if (!empty($quizResultId)) {

                $quizResult = QuizzesResult::where('id', $quizResultId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!empty($quizResult)) {

                    $passMark = $quiz->pass_mark;
                    $totalMark = 0;
                    $status = '';

                    if (!empty($results)) {
                        foreach ($results as $questionId => $result) {

                            if (!is_array($result)) {
                                unset($results[$questionId]);

                            } else {

                                $question = QuizzesQuestion::where('id', $questionId)
                                    ->where('quiz_id', $quiz->id)
                                    ->first();

                                if ($question and !empty($result['answer'])) {
                                    $answer = QuizzesQuestionsAnswer::where('id', $result['answer'])
                                        ->where('question_id', $question->id)
                                        ->where('creator_id', $quiz->creator_id)
                                        ->first();

                                    $results[$questionId]['status'] = false;
                                    $results[$questionId]['grade'] = $question->grade;
                                    $results[$questionId]['negative_grade'] = $question->negative_grade ?? null;

                                    if ($answer && $answer->correct) {
                                        $results[$questionId]['status'] = true;
                                        $totalMark += (int) $question->grade;
                                    } else {
                                        if ($question->type === 'multiple' && !empty($question->negative_grade)) {
                                            $totalMark -= (int) $question->negative_grade;
                                        }
                                    }

                                    if ($question->type == 'descriptive') {
                                        $status = 'waiting';
                                    }
                                }
                            }
                        }
                    }

                    if (empty($status)) {
                        $status = ($totalMark >= $passMark) ? QuizzesResult::$passed : QuizzesResult::$failed;
                    }

                    $results["attempt_number"] = $request->get('attempt_number');

                    $quizResult->update([
                        'results' => json_encode($results),
                        'user_grade' => $totalMark,
                        'status' => $status,
                        'created_at' => time()
                    ]);

                    if ($quizResult->status == QuizzesResult::$waiting) {
                        $notifyOptions = [
                            '[c.title]' => $quiz->webinar ? $quiz->webinar->title : '-',
                            '[student.name]' => $user->full_name,
                            '[q.title]' => $quiz->title,
                        ];
                        sendNotification('waiting_quiz', $notifyOptions, $quiz->creator_id);
                    }

                    if ($quizResult->status == QuizzesResult::$passed) {
                        $passTheQuizReward = RewardAccounting::calculateScore(Reward::PASS_THE_QUIZ);
                        RewardAccounting::makeRewardAccounting($quizResult->user_id, $passTheQuizReward, Reward::PASS_THE_QUIZ, $quiz->id, true);

                        if ($quiz->certificate) {
                            $certificateReward = RewardAccounting::calculateScore(Reward::CERTIFICATE);
                            RewardAccounting::makeRewardAccounting($quizResult->user_id, $certificateReward, Reward::CERTIFICATE, $quiz->id, true);
                        }
                    }

                    return ['action' => 'success', 'quizResult' => $quizResult];
                }
            }
        }
        return ['action' => 'not_found'];
    }

    public function getStatusData($quizResultId, $user)
    {
        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->where('user_id', $user->id)
            ->with(['quiz' => function ($query) {
                $query->with(['quizQuestions']);
            }])
            ->first();
        if (!$quizResult) {
            return null;
        }
        $quiz = $quizResult->quiz;
        $attemptCount = $quiz->attempt;
        $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->count();
        $canTryAgain = false;
        $remainingTryAgain = 0;
        if (empty($attemptCount) or $userQuizDone < $attemptCount) {
            $canTryAgain = true;
            if (empty($attemptCount)) {
                $remainingTryAgain = 'unlimited';
            } else {
                $remainingTryAgain = $attemptCount - $userQuizDone;
            }
        }
        $quizQuestions = $quizResult->getQuestions();
        $totalQuestionsCount = $quizQuestions->count();
        return [
            'pageTitle' => trans('quiz.quiz_status'),
            'quizResult' => $quizResult,
            'quiz' => $quiz,
            'webinar' => $quiz->webinar,
            'quizQuestions' => $quizQuestions,
            'attemptCount' => $userQuizDone,
            'canTryAgain' => $canTryAgain,
            'totalQuestionsCount' => $totalQuestionsCount,
            'remainingTryAgain' => $remainingTryAgain,
        ];
    }

    public function orderItems(Request $request, $quizId, $user)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'items' => 'required',
            'table' => 'required',
        ]);
        if ($validator->fails()) {
            return ['action' => 'validation_error', 'errors' => $validator->errors()];
        }
        $quiz = Quiz::query()->where('id', $quizId)
            ->where('creator_id', $user->id)
            ->first();
        if (!empty($quiz)) {
            $tableName = $data['table'];
            $itemIds = explode(',', $data['items']);
            if (count($itemIds)) {
                switch ($tableName) {
                    case 'quizzes_questions':
                        foreach ($itemIds as $order => $id) {
                            QuizzesQuestion::where('id', $id)
                                ->where('quiz_id', $quiz->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                }
            }
        }
        return ['action' => 'success'];
    }

    private function getQuizStats($quiz, $user, $isStartPage = false)
    {
        $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->get();
        $quizQuestionsQuery = QuizzesQuestion::query()
            ->where('quiz_id', $quiz->id)
            ->with('quizzesQuestionsAnswers');
        if ($quiz->display_questions_randomly) {
            $quizQuestionsQuery->inRandomOrder();
        } else {
            $quizQuestionsQuery->orderBy('order', 'asc');
        }
        if (($quiz->display_limited_questions and !empty($quiz->display_number_of_questions))) {
            $totalQuestionsCount = $quiz->display_number_of_questions;
            $quizQuestions = $quizQuestionsQuery->take($totalQuestionsCount)->get();
        } else {
            $quizQuestions = $quizQuestionsQuery->get();
            $totalQuestionsCount = $quizQuestions->count();
        }
        $attemptCount = $userQuizDone->count();
        if ($isStartPage) {
            $attemptCount = $attemptCount + 1;
        }
        return [
            'attemptCount' => $attemptCount,
            'totalQuestionsCount' => $totalQuestionsCount,
            'quizQuestions' => $quizQuestions,
        ];
    }
}
