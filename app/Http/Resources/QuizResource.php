<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string|null $auth_status
 * @property-read int|null $time
 * @property-read \Illuminate\Database\Eloquent\Collection $quizQuestions
 * @property-read int|null $pass_mark
 * @property-read float|null $average_grade
 * @property-read \Illuminate\Database\Eloquent\Collection $quizResults
 * @property-read \Illuminate\Database\Eloquent\Collection $certificates
 * @property-read int $success_rate
 * @property-read string $status
 * @property-read int|null $attempt
 * @property-read string|null $created_at
 * @property-read bool|int $certificate
 * @property-read \App\Models\Api\User $creator
 * @property-read int|null $auth_attempt_count
 * @property-read string $attempt_state
 * @property-read bool $auth_can_take_quiz
 * @property-read \App\Models\Api\Webinar $webinar
 * @property-read bool|null $AuthPassedQuiz
 */
class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_type' => 'quiz',
            'title' => $this->title,
            'auth_status' => $this->auth_status,
            'can_view_error' => $this->canViewError(),
            'time' => $this->time,
            'question_count' => $this->quizQuestions->count(),
            'total_mark' => $this->quizQuestions->sum('grade'),
            'pass_mark' => $this->pass_mark,
            'average_grade' => $this->average_grade,
            'student_count' => $this->quizResults->pluck('user_id')->count(),
            'certificates_count' => $this->certificates->count(),
            'success_rate' => $this->success_rate,
            'status' => $this->status,
            'attempt' => $this->attempt,
            'created_at' => $this->created_at,
            'certificate' => $this->certificate,
            'teacher' => $this->creator->brief,

            /**********************/

            'auth_attempt_count' => $this->auth_attempt_count,
            'attempt_state' => $this->attempt_state,
            'auth_can_start' => $this->auth_can_take_quiz,
            'webinar' => $this->webinar->brief,
            'check_previous_parts' => null,
            'access_after_day' => null,
            'passed' => $this->AuthPassedQuiz,
        ];


    }
}
