<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\Translation\QuizTranslation;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\User;
use App\Models\Webinar;
use App\Models\QuizzesResult;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\App\QuizListingService;
use App\Services\App\QuizManagementService;
use App\Services\App\QuizAttemptService;

class QuizController extends Controller
{
    protected $quizListingService;
    protected $quizManagementService;
    protected $quizAttemptService;

    public function __construct(QuizListingService $quizListingService, QuizManagementService $quizManagementService, QuizAttemptService $quizAttemptService)
    {
        $this->quizListingService = $quizListingService;
        $this->quizManagementService = $quizManagementService;
        $this->quizAttemptService = $quizAttemptService;
    }

    public function index(Request $request)
    {
        $this->authorize("panel_quizzes_lists");

        $user = auth()->user();

        $result = $this->quizListingService->getIndexData($request, $user, $this->perPage);

        if ($result['action'] === 'ajax') {
            $html = "";
            foreach ($result['quizzes'] as $quiz) {
                $html .= (string)view()->make('design_1.panel.quizzes.lists.table_items', ['quiz' => $quiz]);
            }

            return response()->json([
                'data' => $html,
                'pagination' => $this->makePagination($request, $result['quizzes'], $result['total'], $result['count'], true)
            ]);
        }

        $data = [
            'pageTitle' => trans('quiz.quizzes_list_page_title'),
            'allQuizzesLists' => $result['allQuizzesLists'],
            'quizzes' => $result['quizzes'],
            'pagination' => $this->makePagination($request, $result['quizzes'], $result['total'], $result['count'], true),
        ];
        $data = array_merge($data, $result['topStats']);

        return view('design_1.panel.quizzes.lists.index', $data);
    }
    public function create(Request $request)
    {
        $this->authorize("panel_quizzes_create");

        $data = $this->quizManagementService->getCreateData($request, auth()->user());

        return view('design_1.panel.quizzes.create.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize("panel_quizzes_create");

        $result = $this->quizManagementService->store($request, auth()->user());

        if ($result['action'] === 'validation_error') {
            return response()->json([
                'code' => 422,
                'errors' => $result['errors']
            ], 422);
        }

        if ($request->ajax()) {
            return response()->json([
                'code' => 200,
                'redirect_to' => $result['redirect_to']
            ]);
        } else {
            return redirect()->route('panel_edit_quiz', ['id' => $result['quiz']->id]);
        }
    }

    public function edit(Request $request, $id)
    {
        $this->authorize("panel_quizzes_create");

        $data = $this->quizManagementService->getEditData($request, $id, auth()->user());

        if (!empty($data)) {
            return view('design_1.panel.quizzes.create.index', $data);
        }

        abort(404);
    }

    public function update(Request $request, $id)
    {
        $this->authorize("panel_quizzes_create");

        $result = $this->quizManagementService->update($request, $id, auth()->user());

        if ($result['action'] === 'not_found') {
            abort(404);
        }

        if ($result['action'] === 'validation_error') {
            return response()->json([
                'code' => 422,
                'errors' => $result['errors']
            ], 422);
        }

        if ($request->ajax()) {
            return response()->json([
                'code' => 200
            ]);
        } else {
            return redirect('panel/quizzes');
        }
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize("panel_quizzes_delete");

        $result = $this->quizManagementService->destroy($request, $id, auth()->user());

        if ($result['action'] === 'success') {
            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function overview(Request $request, $id)
    {
        $user = auth()->user();
        $result = $this->quizAttemptService->getOverviewData($request, $id, $user);

        if ($result['action'] === 'not_found') {
            abort(404);
        }
        if ($result['action'] === 'no_access') {
            return back()->with(['toast' => $result['toast']]);
        }
        return view('design_1.panel.quizzes.holding.overview', $result['data']);
    }

    public function start(Request $request, $id)
    {
        $user = auth()->user();
        $result = $this->quizAttemptService->getStartData($request, $id, $user);

        if ($result['action'] === 'not_found') {
            abort(404);
        }
        if (in_array($result['action'], ['no_access', 'expired', 'cant_start'])) {
            return back()->with(['toast' => $result['toast']]);
        }
        return view('design_1.panel.quizzes.holding.start.index', $result['data']);
    }

    public function quizzesStoreResult(Request $request, $id)
    {
        $user = auth()->user();
        $result = $this->quizAttemptService->storeResult($request, $id, $user);

        if ($result['action'] === 'not_found') {
            abort(404);
        }
        return redirect()->route('quiz_status', ['quizResultId' => $result['quizResult']]);
    }

    public function status($quizResultId)
    {
        $user = auth()->user();
        $data = $this->quizAttemptService->getStatusData($quizResultId, $user);

        if (!empty($data)) {
            return view('design_1.panel.quizzes.holding.status.index', $data);
        }
        abort(404);
    }

    public function orderItems(Request $request, $quizId)
    {
        $user = auth()->user();
        $result = $this->quizAttemptService->orderItems($request, $quizId, $user);

        if ($result['action'] === 'validation_error') {
            return response([
                'code' => 422,
                'errors' => $result['errors'],
            ], 422);
        }
        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }

}
