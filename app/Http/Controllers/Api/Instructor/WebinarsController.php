<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Exports\WebinarStudents;
use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use App\Models\FAQ;
use App\Models\File;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\WebinarChapter;
use App\User;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Services\App\InstructorWebinarListingService;
use App\Services\App\InstructorWebinarCreationService;
use App\Services\App\InstructorWebinarUpdateService;
use App\Services\App\InstructorWebinarManagementService;
use App\Services\App\InstructorWebinarMiscService;

class WebinarsController extends Controller
{

    protected $instructorWebinarListingService;
    protected $instructorWebinarCreationService;
    protected $instructorWebinarUpdateService;
    protected $instructorWebinarManagementService;
    protected $instructorWebinarMiscService;

    public function __construct(InstructorWebinarListingService $instructorWebinarListingService, InstructorWebinarCreationService $instructorWebinarCreationService, InstructorWebinarUpdateService $instructorWebinarUpdateService, InstructorWebinarManagementService $instructorWebinarManagementService, InstructorWebinarMiscService $instructorWebinarMiscService)
    {
        $this->instructorWebinarListingService = $instructorWebinarListingService;
        $this->instructorWebinarCreationService = $instructorWebinarCreationService;
        $this->instructorWebinarUpdateService = $instructorWebinarUpdateService;
        $this->instructorWebinarManagementService = $instructorWebinarManagementService;
        $this->instructorWebinarMiscService = $instructorWebinarMiscService;
    }

    public function index(Request $request)
    {
        $data = $this->instructorWebinarListingService->index($request);

        return view(getTemplate() . '.panel.webinar.index', $data);
    }

    public function invitations(Request $request)
    {
        $data = $this->instructorWebinarListingService->invitations($request);

        return view(getTemplate() . '.panel.webinar.index', $data);
    }

    public function organizationClasses(Request $request)
    {
        $data = $this->instructorWebinarListingService->organizationClasses($request);

        return view(getTemplate() . '.panel.webinar.organization_classes', $data);
    }

    public function create(Request $request)
    {
        $data = $this->instructorWebinarCreationService->create($request);

        return view(getTemplate() . '.panel.webinar.create', $data);
    }

    public function store(Request $request)
    {
        return $this->instructorWebinarCreationService->store($request);
    }

    public function storeAll(Request $request)
    {
        return $this->instructorWebinarCreationService->storeAll($request);
    }

    public function update(Request $request, $id)
    {
        $result = $this->instructorWebinarUpdateService->update($request, $id);

        if (isset($result['withErrors'])) {
            return redirect($result['redirect'])->withErrors($result['withErrors']);
        }

        return redirect($result['redirect']);
    }

    public function edit($id, $step = 1)
    {
        $data = $this->instructorWebinarUpdateService->edit($id, $step);

        return view(getTemplate() . '.panel.webinar.create', $data);
    }

    public function updateAll(Request $request, $id)
    {
        $result = $this->instructorWebinarUpdateService->updateAll($request, $id);

        if (isset($result['withErrors'])) {
            return redirect($result['redirect'])->withErrors($result['withErrors']);
        }

        return redirect($result['redirect']);
    }

    public function destroy(Request $request, $id)
    {
        return $this->instructorWebinarManagementService->destroy($request, $id);
    }
    public function duplicate($id)
    {
        return $this->instructorWebinarManagementService->duplicate($id);
    }
    public function exportStudentsList($id)
    {
        return $this->instructorWebinarManagementService->exportStudentsList($id);
    }
    public function search(Request $request)
    {
        return $this->instructorWebinarMiscService->search($request);
    }
    public function getTags(Request $request, $id)
    {
        return $this->instructorWebinarMiscService->getTags($request, $id);
    }
    public function invoice($id)
    {
        return $this->instructorWebinarMiscService->invoice($id);
    }
    public function purchases(Request $request)
    {
        return $this->instructorWebinarMiscService->purchases($request);
    }
    public function getJoinInfo(Request $request)
    {
        return $this->instructorWebinarMiscService->getJoinInfo($request);
    }
    public function getNextSessionInfo($id)
    {
        return $this->instructorWebinarMiscService->getNextSessionInfo($id);
    }
    public function orderItems(Request $request)
    {
        return $this->instructorWebinarMiscService->orderItems($request);
    }
}
