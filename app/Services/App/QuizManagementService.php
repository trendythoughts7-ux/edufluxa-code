<?php

namespace App\Services\App;

use App\Http\Controllers\MainTraits\FilesTraits;
use App\Models\Quiz;
use App\Models\Translation\QuizTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizManagementService
{
    use FilesTraits;

    /**
     * Data needed to render the "create quiz" page.
     */
    public function getCreateData(Request $request, $user)
    {
        $webinars = Webinar::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhere('creator_id', $user->id)
                ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
        })->get();

        $locale = $request->get('locale', app()->getLocale());

        return [
            'pageTitle' => trans('quiz.new_quiz_page_title'),
            'webinars' => $webinars,
            'userLanguages' => getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];
    }

    /**
     * Store a new quiz. Returns a structured array for the controller to act on.
     */
    public function store(Request $request, $user)
    {
        $data = $request->get('ajax')['new'];
        $locale = $request->get('locale', getDefaultLocale());

        $validate = Validator::make($data, [
            'title' => 'required|max:255',
            'webinar_id' => 'required|exists:webinars,id',
            'chapter_id' => 'required|exists:webinar_chapters,id',
            'pass_mark' => 'required',
        ]);

        if ($validate->fails()) {
            return [
                'action' => 'validation_error',
                'errors' => $validate->errors(),
            ];
        }

        $webinar = null;
        $chapter = null;

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::query()->where('id', $data['webinar_id'])
                ->where(function ($query) use ($user) {
                    $query->where('teacher_id', $user->id)
                        ->orWhere('creator_id', $user->id)
                        ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                            $query->where('teacher_id', $user->id);
                        });
                })->first();

            if (!empty($webinar) and !empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }
        }

        $quiz = Quiz::create([
            'webinar_id' => !empty($webinar) ? $webinar->id : null,
            'chapter_id' => !empty($chapter) ? $chapter->id : null,
            'creator_id' => $user->id,
            'attempt' => $data['attempt'] ?? null,
            'pass_mark' => $data['pass_mark'],
            'time' => $data['time'] ?? null,
            'status' => (!empty($data['status']) and $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
            'certificate' => (!empty($data['certificate']) and $data['certificate'] == 'on'),
            'display_questions_randomly' => (!empty($data['display_questions_randomly']) and $data['display_questions_randomly'] == 'on'),
            'expiry_days' => (!empty($data['expiry_days']) and $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
            'created_at' => time(),
        ]);

        if (!empty($quiz)) {
            QuizTranslation::updateOrCreate([
                'quiz_id' => $quiz->id,
                'locale' => mb_strtolower($locale),
            ], [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            if (!empty($quiz->chapter_id)) {
                WebinarChapterItem::makeItem($quiz->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }

            $this->handleIcon($request, $quiz);
        }

        // Send Notification To All Students
        if (!empty($webinar)) {
            $webinar->sendNotificationToAllStudentsForNewQuizPublished($quiz);
        }

        if (!empty($webinar)) {
            unset($webinar->title, $webinar->locale);
            $webinar->update([
                'updated_at' => time()
            ]);
        }

        $redirectUrl = '';
        if (empty($data['is_webinar_page'])) {
            $redirectUrl = '/panel/quizzes/' . $quiz->id . '/edit';
        }

        return [
            'action' => 'success',
            'quiz' => $quiz,
            'redirect_to' => $redirectUrl,
        ];
    }

    /**
     * Data needed to render the "edit quiz" page. Returns null if not found/authorized.
     */
    public function getEditData(Request $request, $id, $user)
    {
        $webinars = Webinar::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhere('creator_id', $user->id)
                ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
        })->get();

        $webinarIds = $webinars->pluck('id')->toArray();

        $quiz = Quiz::where('id', $id)
            ->where('creator_id', $user->id)
            ->where(function ($query) use ($user, $webinarIds) {
                $query->where('creator_id', $user->id);
                $query->orWhereIn('webinar_id', $webinarIds);
            })
            ->with([
                'quizQuestions' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with('quizzesQuestionsAnswers');
                },
            ])->first();

        if (empty($quiz)) {
            return null;
        }

        $chapters = collect();

        if (!empty($quiz->webinar)) {
            $chapters = $quiz->webinar->chapters;
        }

        $locale = $request->get('locale', app()->getLocale());

        return [
            'pageTitle' => trans('public.edit') . ' ' . $quiz->title,
            'webinars' => $webinars,
            'quiz' => $quiz,
            'quizQuestions' => $quiz->quizQuestions,
            'chapters' => $chapters,
            'userLanguages' => getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];
    }

    /**
     * Update an existing quiz. Returns a structured array for the controller to act on.
     */
    public function update(Request $request, $id, $user)
    {
        $data = $request->get('ajax')[$id];

        $webinar = null;
        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])
                ->where(function ($query) use ($user) {
                    $query->where('teacher_id', $user->id)
                        ->orWhere('creator_id', $user->id)
                        ->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                            $query->where('teacher_id', $user->id);
                        });
                })->first();
        }

        $quiz = Quiz::query()->where('id', $id)
            ->where(function ($query) use ($user, $webinar) {
                $query->where('creator_id', $user->id);

                if (!empty($webinar)) {
                    $query->orWhere('webinar_id', $webinar->id);
                }
            })
            ->first();

        if (empty($quiz)) {
            return ['action' => 'not_found'];
        }

        $quizQuestionsCount = $quiz->quizQuestions->count();

        $locale = $request->get('locale', getDefaultLocale());

        $rules = [
            'title' => 'required|max:255',
            'webinar_id' => 'required|exists:webinars,id',
            'chapter_id' => 'required|exists:webinar_chapters,id',
            'pass_mark' => 'required',
            'display_number_of_questions' => 'required_if:display_limited_questions,on|nullable|between:1,' . $quizQuestionsCount
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return [
                'action' => 'validation_error',
                'errors' => $validate->errors(),
            ];
        }

        $chapter = null;
        if (!empty($webinar) and !empty($data['chapter_id'])) {
            $chapter = WebinarChapter::where('id', $data['chapter_id'])
                ->where('webinar_id', $webinar->id)
                ->first();
        }

        $quiz->update([
            'webinar_id' => !empty($webinar) ? $webinar->id : null,
            'chapter_id' => !empty($chapter) ? $chapter->id : null,
            'attempt' => $data['attempt'] ?? null,
            'pass_mark' => $data['pass_mark'],
            'time' => $data['time'] ?? null,
            'status' => (!empty($data['status']) and $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
            'certificate' => (!empty($data['certificate']) and $data['certificate'] == 'on'),
            'display_limited_questions' => (!empty($data['display_limited_questions']) and $data['display_limited_questions'] == 'on'),
            'display_number_of_questions' => (!empty($data['display_limited_questions']) and $data['display_limited_questions'] == 'on' and !empty($data['display_number_of_questions'])) ? $data['display_number_of_questions'] : null,
            'display_questions_randomly' => (!empty($data['display_questions_randomly']) and $data['display_questions_randomly'] == 'on'),
            'expiry_days' => (!empty($data['expiry_days']) and $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
            'updated_at' => time(),
        ]);

        $checkChapterItem = WebinarChapterItem::query()
            ->where('item_id', $quiz->id)
            ->where('type', WebinarChapterItem::$chapterQuiz)
            ->first();

        if (!empty($quiz->chapter_id)) {
            if (empty($checkChapterItem)) {
                WebinarChapterItem::makeItem($quiz->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            } elseif ($checkChapterItem->chapter_id != $quiz->chapter_id) {
                $checkChapterItem->delete(); // remove quiz from old chapter and assign it to new chapter

                WebinarChapterItem::makeItem($quiz->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }
        } else if (!empty($checkChapterItem)) {
            $checkChapterItem->delete();
        }

        QuizTranslation::updateOrCreate([
            'quiz_id' => $quiz->id,
            'locale' => mb_strtolower($locale),
        ], [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $this->handleIcon($request, $quiz);

        if (!empty($webinar)) {
            unset($webinar->title, $webinar->locale);
            $webinar->update([
                'updated_at' => time()
            ]);
        }

        return [
            'action' => 'success',
            'quiz' => $quiz,
        ];
    }

    /**
     * Delete a quiz. Returns a structured array for the controller to act on.
     */
    public function destroy(Request $request, $id, $user)
    {
        $quiz = Quiz::where('id', $id)
            ->first();

        if (!empty($quiz)) {

            $webinar = null;
            if (!empty($quiz->webinar_id)) {
                $webinar = Webinar::query()->find($quiz->webinar_id);
            }

            if ($quiz->creator_id == $user->id or (!empty($webinar) and $webinar->canAccess($user))) {
                if ($quiz->delete()) {
                    $checkChapterItem = WebinarChapterItem::where('user_id', $user->id)
                        ->where('item_id', $id)
                        ->where('type', WebinarChapterItem::$chapterQuiz)
                        ->first();

                    if (!empty($checkChapterItem)) {
                        $checkChapterItem->delete();
                    }

                    return ['action' => 'success'];
                }
            }
        }

        return ['action' => 'failed'];
    }

    /**
     * Handle quiz icon upload/removal. Private — only reachable via store()/update().
     */
    private function handleIcon(Request $request, $quiz)
    {
        $iconPath = $quiz->icon ?? null;

        if (!empty($request->file('icon'))) {
            if (!empty($iconPath)) {
                $this->removeFile($iconPath);
            }

            $iconPath = $this->uploadFile($request->file('icon'), "quizzes/{$quiz->id}", 'icon', $quiz->creator_id);
        }

        $quiz->update([
            'icon' => $iconPath
        ]);

        return $quiz;
    }
}
