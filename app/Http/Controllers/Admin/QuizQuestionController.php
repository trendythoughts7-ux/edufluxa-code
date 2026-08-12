<?php // @phpstan-ignore-line
 // @phpstan-ignore-line
namespace App\Http\Controllers\Admin; // @phpstan-ignore-line
 // @phpstan-ignore-line
use App\Http\Controllers\Controller; // @phpstan-ignore-line
use App\Models\QuizzesQuestion; // @phpstan-ignore-line
use App\Models\QuizzesQuestionsAnswer; // @phpstan-ignore-line
use App\Models\Translation\QuizzesQuestionsAnswerTranslation; // @phpstan-ignore-line
use App\Models\Translation\QuizzesQuestionTranslation; // @phpstan-ignore-line
use Illuminate\Http\Request; // @phpstan-ignore-line
use App\Models\Quiz; // @phpstan-ignore-line
use Illuminate\Support\Facades\Validator; // @phpstan-ignore-line
 // @phpstan-ignore-line
class QuizQuestionController extends Controller // @phpstan-ignore-line
{ // @phpstan-ignore-line
    public function store(Request $request) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $data = $request->get('ajax'); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $rules = [ // @phpstan-ignore-line
            'quiz_id' => 'required|exists:quizzes,id', // @phpstan-ignore-line
            'title' => 'required', // @phpstan-ignore-line
            'grade' => 'required|integer', // @phpstan-ignore-line
            'type' => 'required', // @phpstan-ignore-line
            'image' => 'nullable|max:255', // @phpstan-ignore-line
            'video' => 'nullable|max:255', // @phpstan-ignore-line
            // optional negative mark for multiple-choice questions // @phpstan-ignore-line
            'negative_grade' => 'nullable|integer|min:0', // @phpstan-ignore-line
        ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
        $validate = Validator::make($data, $rules); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($validate->fails()) { // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => $validate->errors() // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($data['image']) and !empty($data['video'])) { // @phpstan-ignore-line
 // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => [ // @phpstan-ignore-line
                    'image' => [trans('update.quiz_question_image_validation_by_video')], // @phpstan-ignore-line
                    'video' => [trans('update.quiz_question_image_validation_by_video')], // @phpstan-ignore-line
                ] // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
 // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) { // @phpstan-ignore-line
            $answers = $data['answers']; // @phpstan-ignore-line
 // @phpstan-ignore-line
            $hasCorrect = false; // @phpstan-ignore-line
            foreach ($answers as $answer) { // @phpstan-ignore-line
                if (isset($answer['correct'])) { // @phpstan-ignore-line
                    $hasCorrect = true; // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!$hasCorrect) { // @phpstan-ignore-line
                return response([ // @phpstan-ignore-line
                    'code' => 422, // @phpstan-ignore-line
                    'errors' => [ // @phpstan-ignore-line
                        'current_answer' => [trans('quiz.current_answer_required')] // @phpstan-ignore-line
                    ], // @phpstan-ignore-line
                ], 422); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        $quiz = Quiz::where('id', $data['quiz_id'])->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($quiz)) { // @phpstan-ignore-line
            $creator = $quiz->creator; // @phpstan-ignore-line
            $order = QuizzesQuestion::query()->where('quiz_id', $quiz->id)->count() + 1; // @phpstan-ignore-line
 // @phpstan-ignore-line
            $quizQuestion = QuizzesQuestion::create([ // @phpstan-ignore-line
                'quiz_id' => $data['quiz_id'], // @phpstan-ignore-line
                'creator_id' => $creator->id, // @phpstan-ignore-line
                'grade' => $data['grade'], // @phpstan-ignore-line
                'negative_grade' => $data['negative_grade'] ?? null, // @phpstan-ignore-line
                'type' => $data['type'], // @phpstan-ignore-line
                'image' => $data['image'] ?? null, // @phpstan-ignore-line
                'video' => $data['video'] ?? null, // @phpstan-ignore-line
                'order' => $order, // @phpstan-ignore-line
                'created_at' => time() // @phpstan-ignore-line
            ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!empty($quizQuestion)) { // @phpstan-ignore-line
                QuizzesQuestionTranslation::updateOrCreate([ // @phpstan-ignore-line
                    'quizzes_question_id' => $quizQuestion->id, // @phpstan-ignore-line
                    'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                ], [ // @phpstan-ignore-line
                    'title' => $data['title'], // @phpstan-ignore-line
                    'correct' => $data['correct'] ?? null, // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            $quiz->increaseTotalMark($quizQuestion->grade); // @phpstan-ignore-line
 // @phpstan-ignore-line
            if ($quizQuestion->type == QuizzesQuestion::$multiple and !empty($data['answers'])) { // @phpstan-ignore-line
 // @phpstan-ignore-line
                foreach ($answers as $answer) { // @phpstan-ignore-line
                    if (!empty($answer['title']) or !empty($answer['file'])) { // @phpstan-ignore-line
                        $questionAnswer = QuizzesQuestionsAnswer::create([ // @phpstan-ignore-line
                            'question_id' => $quizQuestion->id, // @phpstan-ignore-line
                            'creator_id' => $creator->id, // @phpstan-ignore-line
                            'image' => $answer['file'] ?? null, // @phpstan-ignore-line
                            'correct' => isset($answer['correct']) ? true : false, // @phpstan-ignore-line
                            'created_at' => time() // @phpstan-ignore-line
                        ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                        if (!empty($questionAnswer)) { // @phpstan-ignore-line
                            QuizzesQuestionsAnswerTranslation::updateOrCreate([ // @phpstan-ignore-line
                                'quizzes_questions_answer_id' => $questionAnswer->id, // @phpstan-ignore-line
                                'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                            ], [ // @phpstan-ignore-line
                                'title' => $answer['title'], // @phpstan-ignore-line
                            ]); // @phpstan-ignore-line
                        } // @phpstan-ignore-line
                    } // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'code' => 200 // @phpstan-ignore-line
            ], 200); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([ // @phpstan-ignore-line
            'code' => 422 // @phpstan-ignore-line
        ], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function edit($question_id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $question = QuizzesQuestion::where('id', $question_id)->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($question)) { // @phpstan-ignore-line
            $quiz = Quiz::find($question->quiz_id); // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!empty($quiz)) { // @phpstan-ignore-line
                $locale = app()->getLocale(); // @phpstan-ignore-line
 // @phpstan-ignore-line
                $data = [ // @phpstan-ignore-line
                    'pageTitle' => $question->title, // @phpstan-ignore-line
                    'quiz' => $quiz, // @phpstan-ignore-line
                    'question_edit' => $question, // @phpstan-ignore-line
                    'locale' => mb_strtolower($locale), // @phpstan-ignore-line
                    'defaultLocale' => getDefaultLocale(), // @phpstan-ignore-line
                ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($question->type == 'multiple') { // @phpstan-ignore-line
                    $html = \View::make('admin.quizzes.modals.multiple_question', $data)->render(); // @phpstan-ignore-line
                } else { // @phpstan-ignore-line
                    $html = \View::make('admin.quizzes.modals.descriptive_question', $data)->render(); // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                return response()->json([ // @phpstan-ignore-line
                    'html' => $html // @phpstan-ignore-line
                ], 200); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function getQuestionByLocale(Request $request, $id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $user = auth()->user(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $question = QuizzesQuestion::where('id', $id) // @phpstan-ignore-line
            ->with('quizzesQuestionsAnswers') // @phpstan-ignore-line
            ->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($question)) { // @phpstan-ignore-line
            $locale = $request->get('locale', app()->getLocale()); // @phpstan-ignore-line
 // @phpstan-ignore-line
            foreach ($question->translatedAttributes as $attribute) { // @phpstan-ignore-line
                try { // @phpstan-ignore-line
                    $question->$attribute = $question->translate(mb_strtolower($locale))->$attribute; // @phpstan-ignore-line
                } catch (\Exception $e) { // @phpstan-ignore-line
                    $question->$attribute = null; // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!empty($question->quizzesQuestionsAnswers) and count($question->quizzesQuestionsAnswers)) { // @phpstan-ignore-line
                foreach ($question->quizzesQuestionsAnswers as $answer) { // @phpstan-ignore-line
                    foreach ($answer->translatedAttributes as $att) { // @phpstan-ignore-line
                        try { // @phpstan-ignore-line
                            $answer->$att = $answer->translate(mb_strtolower($locale))->$att; // @phpstan-ignore-line
                        } catch (\Exception $e) { // @phpstan-ignore-line
                            $answer->$att = null; // @phpstan-ignore-line
                        } // @phpstan-ignore-line
                    } // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'question' => $question // @phpstan-ignore-line
            ], 200); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function update(Request $request, $id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        $data = $request->get('ajax'); // @phpstan-ignore-line
 // @phpstan-ignore-line
        $rules = [ // @phpstan-ignore-line
            'quiz_id' => 'required|exists:quizzes,id', // @phpstan-ignore-line
            'title' => 'required', // @phpstan-ignore-line
            'grade' => 'required', // @phpstan-ignore-line
            'type' => 'required', // @phpstan-ignore-line
            'image' => 'nullable|max:255', // @phpstan-ignore-line
            'video' => 'nullable|max:255', // @phpstan-ignore-line
            'negative_grade' => 'nullable|integer|min:0', // @phpstan-ignore-line
        ]; // @phpstan-ignore-line
 // @phpstan-ignore-line
        $validate = Validator::make($data, $rules); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($validate->fails()) { // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => $validate->errors() // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($data['image']) and !empty($data['video'])) { // @phpstan-ignore-line
            return response()->json([ // @phpstan-ignore-line
                'code' => 422, // @phpstan-ignore-line
                'errors' => [ // @phpstan-ignore-line
                    'image' => [trans('update.quiz_question_image_validation_by_video')], // @phpstan-ignore-line
                    'video' => [trans('update.quiz_question_image_validation_by_video')], // @phpstan-ignore-line
                ] // @phpstan-ignore-line
            ], 422); // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) { // @phpstan-ignore-line
            $answers = $data['answers']; // @phpstan-ignore-line
 // @phpstan-ignore-line
            $hasCorrect = false; // @phpstan-ignore-line
            foreach ($answers as $answer) { // @phpstan-ignore-line
                if (isset($answer['correct'])) { // @phpstan-ignore-line
                    $hasCorrect = true; // @phpstan-ignore-line
                } // @phpstan-ignore-line
            } // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!$hasCorrect) { // @phpstan-ignore-line
                return response([ // @phpstan-ignore-line
                    'code' => 422, // @phpstan-ignore-line
                    'errors' => [ // @phpstan-ignore-line
                        'current_answer' => [trans('quiz.current_answer_required')] // @phpstan-ignore-line
                    ], // @phpstan-ignore-line
                ], 422); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        $quiz = Quiz::where('id', $data['quiz_id'])->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        if (!empty($quiz)) { // @phpstan-ignore-line
            $creator = $quiz->creator; // @phpstan-ignore-line
            $quizQuestion = QuizzesQuestion::where('id', $id) // @phpstan-ignore-line
                ->where('quiz_id', $quiz->id) // @phpstan-ignore-line
                ->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
            if (!empty($quizQuestion) and !empty($creator)) { // @phpstan-ignore-line
                $quiz->decreaseTotalMark($quizQuestion->grade); // @phpstan-ignore-line
 // @phpstan-ignore-line
                $quizQuestion->update([ // @phpstan-ignore-line
                    'quiz_id' => $data['quiz_id'], // @phpstan-ignore-line
                    'grade' => $data['grade'], // @phpstan-ignore-line
                    'negative_grade' => $data['negative_grade'] ?? null, // @phpstan-ignore-line
                    'type' => $data['type'], // @phpstan-ignore-line
                    'image' => $data['image'] ?? null, // @phpstan-ignore-line
                    'video' => $data['video'] ?? null, // @phpstan-ignore-line
                    'updated_at' => time() // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                QuizzesQuestionTranslation::updateOrCreate([ // @phpstan-ignore-line
                    'quizzes_question_id' => $quizQuestion->id, // @phpstan-ignore-line
                    'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                ], [ // @phpstan-ignore-line
                    'title' => $data['title'], // @phpstan-ignore-line
                    'correct' => $data['correct'] ?? null, // @phpstan-ignore-line
                ]); // @phpstan-ignore-line
 // @phpstan-ignore-line
                $quiz->increaseTotalMark($quizQuestion->grade); // @phpstan-ignore-line
 // @phpstan-ignore-line
 // @phpstan-ignore-line
                if ($quizQuestion->type == QuizzesQuestion::$multiple and $answers) { // @phpstan-ignore-line
                    $oldAnswerIds = QuizzesQuestionsAnswer::where('question_id', $quizQuestion->id)->pluck('id')->toArray(); // @phpstan-ignore-line
 // @phpstan-ignore-line
                    foreach ($answers as $key => $answer) { // @phpstan-ignore-line
                        if (!empty($answer['title']) or !empty($answer['file'])) { // @phpstan-ignore-line
 // @phpstan-ignore-line
                            if (count($oldAnswerIds)) { // @phpstan-ignore-line
                                $oldAnswerIds = array_filter($oldAnswerIds, function ($item) use ($key) { // @phpstan-ignore-line
                                    return $item != $key; // @phpstan-ignore-line
                                }); // @phpstan-ignore-line
                            } // @phpstan-ignore-line
 // @phpstan-ignore-line
 // @phpstan-ignore-line
                            $quizQuestionsAnswer = QuizzesQuestionsAnswer::where('id', $key)->first(); // @phpstan-ignore-line
 // @phpstan-ignore-line
                            if (!empty($quizQuestionsAnswer)) { // @phpstan-ignore-line
                                $quizQuestionsAnswer->update([ // @phpstan-ignore-line
                                    'question_id' => $quizQuestion->id, // @phpstan-ignore-line
                                    'creator_id' => $creator->id, // @phpstan-ignore-line
                                    'image' => $answer['file'] ?? null, // @phpstan-ignore-line
                                    'correct' => isset($answer['correct']) ? true : false, // @phpstan-ignore-line
                                    'created_at' => time() // @phpstan-ignore-line
                                ]); // @phpstan-ignore-line
                            } else { // @phpstan-ignore-line
                                $quizQuestionsAnswer = QuizzesQuestionsAnswer::create([ // @phpstan-ignore-line
                                    'question_id' => $quizQuestion->id, // @phpstan-ignore-line
                                    'creator_id' => $creator->id, // @phpstan-ignore-line
                                    'image' => $answer['file'], // @phpstan-ignore-line
                                    'correct' => isset($answer['correct']) ? true : false, // @phpstan-ignore-line
                                    'created_at' => time() // @phpstan-ignore-line
                                ]); // @phpstan-ignore-line
                            } // @phpstan-ignore-line
 // @phpstan-ignore-line
                            if ($quizQuestionsAnswer) { // @phpstan-ignore-line
                                QuizzesQuestionsAnswerTranslation::updateOrCreate([ // @phpstan-ignore-line
                                    'quizzes_questions_answer_id' => $quizQuestionsAnswer->id, // @phpstan-ignore-line
                                    'locale' => mb_strtolower($data['locale']), // @phpstan-ignore-line
                                ], [ // @phpstan-ignore-line
                                    'title' => $answer['title'], // @phpstan-ignore-line
                                ]); // @phpstan-ignore-line
                            } // @phpstan-ignore-line
                        } // @phpstan-ignore-line
                    } // @phpstan-ignore-line
 // @phpstan-ignore-line
                    if (count($oldAnswerIds)) { // @phpstan-ignore-line
                        QuizzesQuestionsAnswer::whereIn('id', $oldAnswerIds)->delete(); // @phpstan-ignore-line
                    } // @phpstan-ignore-line
                } // @phpstan-ignore-line
 // @phpstan-ignore-line
                removeContentLocale(); // @phpstan-ignore-line
 // @phpstan-ignore-line
                return response()->json([ // @phpstan-ignore-line
                    'code' => 200 // @phpstan-ignore-line
                ], 200); // @phpstan-ignore-line
            } // @phpstan-ignore-line
        } // @phpstan-ignore-line
 // @phpstan-ignore-line
        removeContentLocale(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        return response()->json([ // @phpstan-ignore-line
            'code' => 422 // @phpstan-ignore-line
        ], 422); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
    public function destroy(Request $request, $id) // @phpstan-ignore-line
    { // @phpstan-ignore-line
        QuizzesQuestion::where('id', $id) // @phpstan-ignore-line
            ->delete(); // @phpstan-ignore-line
 // @phpstan-ignore-line
        return redirect()->back(); // @phpstan-ignore-line
    } // @phpstan-ignore-line
 // @phpstan-ignore-line
} // @phpstan-ignore-line
