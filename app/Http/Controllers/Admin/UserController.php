<?php

namespace App\Http\Controllers\Admin;

use App\Bitwise\UserLevelOfTraining;
use App\Exports\InstructorsExport;
use App\Exports\OrganizationsExport;
use App\Exports\StudentsExport;
use App\Exports\UsersExport;
use App\Jobs\ProcessUserExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\UserFormFieldsTrait;
use App\Mixins\Geo\Geo;
use App\Models\Badge;
use App\Models\BecomeInstructor;
use App\Models\Category;
use App\Models\Export;
use App\Jobs\ProcessGenericExport;
use App\Models\ForumTopic;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Meeting;
use App\Models\Region;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UserBadge;
use App\Models\UserBank;
use App\Models\UserCommission;
use App\Models\UserLoginHistory;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use App\Models\UserRegistrationPackage;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Services\App\UserPurchaseHistoryService;
use App\Services\App\UserFilterService;
use App\Services\App\UserEnrichmentService;
use App\Services\App\UserCommissionService;
use App\Services\App\UserManagementService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use UserFormFieldsTrait;

    public function staffs(Request $request)
    {
        $this->authorize('admin_staffs_list');

        $staffsRoles = Role::where('is_admin', true)->get();
        $staffsRoleIds = $staffsRoles->pluck('id')->toArray();


        $query = User::whereIn('role_id', $staffsRoleIds);
        $userFilterService = new UserFilterService();
        $query = $userFilterService->filters($query, $request);

        $users = $query->orderBy('users.created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.staff_list_title'),
            'users' => $users,
            'staffsRoles' => $staffsRoles,
        ];

        return view('admin.users.staffs', $data);
    }

    public function organizations(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_organizations_list');

        $query = User::where('role_name', Role::$organization);

        $totalOrganizations = deepClone($query)->count();
        $verifiedOrganizations = deepClone($query)->where('verified', true)
            ->count();
        $totalOrganizationsTeachers = User::where('role_name', Role::$teacher)
            ->whereNotNull('organ_id')
            ->count();
        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();


        $userFilterService = new UserFilterService();
        $query = $userFilterService->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('users.created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('users.created_at', 'desc')
                ->paginate(10);
        }


        $userEnrichmentService = new UserEnrichmentService();
        $users = $userEnrichmentService->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('admin/main.organizations'),
            'users' => $users,
            'totalOrganizations' => $totalOrganizations,
            'verifiedOrganizations' => $verifiedOrganizations,
            'totalOrganizationsTeachers' => $totalOrganizationsTeachers,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
        ];

        return view('admin.users.organizations', $data);
    }

    public function students(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::where('role_name', Role::$user);

        $totalStudents = deepClone($query)->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();


        $userFilterService = new UserFilterService();
        $query = $userFilterService->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('users.created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('users.created_at', 'desc')
                ->paginate(10);
        }

        $userEnrichmentService = new UserEnrichmentService();
        $users = $userEnrichmentService->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('public.students'),
            'users' => $users,
            'totalStudents' => $totalStudents,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.users.students', $data);
    }

    public function instructors(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_instructors_list');

        $query = User::where('role_name', Role::$teacher);

        $totalInstructors = deepClone($query)->count();
        $inactiveInstructors = deepClone($query)->where('status', 'inactive')
            ->count();
        $banInstructors = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsInstructors = User::where('role_name', Role::$teacher)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();


        $userFilterService = new UserFilterService();
        $query = $userFilterService->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('users.created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('users.created_at', 'desc')
                ->paginate(10);
        }

        $userEnrichmentService = new UserEnrichmentService();
        $users = $userEnrichmentService->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('admin/main.instructors'),
            'users' => $users,
            'totalInstructors' => $totalInstructors,
            'inactiveInstructors' => $inactiveInstructors,
            'banInstructors' => $banInstructors,
            'totalOrganizationsInstructors' => $totalOrganizationsInstructors,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('admin.users.instructors', $data);
    }





    public function create()
    {
        $this->authorize('admin_users_create');

        $roles = Role::orderBy('created_at', 'desc')->get();
        $userGroups = Group::orderBy('created_at', 'desc')->where('status', 'active')->get();

        $data = [
            'pageTitle' => trans('admin/main.user_new_page_title'),
            'roles' => $roles,
            'userGroups' => $userGroups,
        ];


        return view('admin.users.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_users_create');
        $data = $request->all();

        $userManagementService = new UserManagementService();
        $emailOrMobile = $userManagementService->determineEmailOrMobile($data);
        $data[$emailOrMobile] = $data['email_or_mobile'];
        $request->merge([$emailOrMobile => $data['email_or_mobile']]);
        unset($data['email_or_mobile']);

        $this->validate($request, [
            $emailOrMobile => ($emailOrMobile == 'mobile') ? 'required|numeric|unique:users' : 'required|string|email|max:255|unique:users',
            'full_name' => 'required|min:3|max:255',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'status' => 'required',
        ]);

        $user = $userManagementService->createUser($data, $emailOrMobile);

        if (!empty($user)) {
            return redirect(getAdminPanelUrl() . '/users/' . $user->id . '/edit');
        }

        $toastData = [
            'title' => '',
            'msg' => 'Role not find!',
            'status' => 'error'
        ];
        return back()->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()
            ->where('id', $id)
            ->with([
                'customBadges' => function ($query) {
                    $query->with('badge');
                },
                'occupations' => function ($query) {
                    $query->with('category');
                },
                'organization' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'userRegistrationPackage'
            ])
            ->first();

        if (empty($user)) {
            abort(404);
        }

        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        $becomeInstructor = null;
        $becomeInstructorForm = null;
        $becomeInstructorFormFieldsSubmissions = null;

        if (!empty($request->get('type')) and $request->get('type') == 'check_instructor_request') {
            $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                ->first();

            if (!empty($becomeInstructor)) {
                $becomeInstructorForm = $this->getFormFieldsByType('become_instructor');

                $becomeInstructorFormFieldsSubmissions = \App\Models\UserFormField::query()->where('become_instructor_id', $becomeInstructor->id)->get();
                //$becomeInstructorFormFieldValues = $this->getBecomeInstructorFormFieldValues($becomeInstructor);
            }
        }

        $categories = Category::getCategories();

        $occupations = $user->occupations->pluck('category_id')->toArray();

        $userBadges = $user->getBadges(false);

        $roles = Role::all();
        $badges = Badge::all();

        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }


        $provinces = null;
        $cities = null;
        $districts = null;

        $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
            ->where('type', Region::$country)
            ->get();

        if (!empty($user->country_id)) {
            $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$province)
                ->where('country_id', $user->country_id)
                ->get();
        }

        if (!empty($user->province_id)) {
            $cities = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$city)
                ->where('province_id', $user->province_id)
                ->get();
        }

        if (!empty($user->city_id)) {
            $districts = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$district)
                ->where('city_id', $user->city_id)
                ->get();
        }

        $userBanks = UserBank::query()
            ->with([
                'specifications'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $userType = "organization";
        if ($user->isTeacher()) {
            $userType = "teacher";
        } elseif ($user->isUser()) {
            $userType = "user";
        }

        $formFieldsHtml = $this->getFormFieldsByUserType($request, $userType, true, $user);

        $userLoginHistories = null;

        if ($request->get('tab') == "loginHistory") {
            $userLoginHistories = UserLoginHistory::query()->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        $data = [
            'pageTitle' => trans('admin/pages/users.edit_page_title'),
            'user' => $user,
            'userBadges' => $userBadges,
            'roles' => $roles,
            'badges' => $badges,
            'categories' => $categories,
            'occupations' => $occupations,
            'becomeInstructor' => $becomeInstructor,
            'userLanguages' => $userLanguages,
            'userRegistrationPackage' => $user->userRegistrationPackage,
            'countries' => $countries,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'userBanks' => $userBanks,
            'formFieldsHtml' => $formFieldsHtml,
            'becomeInstructorForm' => $becomeInstructorForm,
            'becomeInstructorFormFieldsSubmissions' => $becomeInstructorFormFieldsSubmissions,
            'userLoginHistories' => $userLoginHistories,
        ];

        // Purchased Classes Data
        $userPurchaseHistoryService = new UserPurchaseHistoryService();
        $data = array_merge($data, $userPurchaseHistoryService->getPurchasedClassesData($user));

        // Purchased Bundles Data
        $data = array_merge($data, $userPurchaseHistoryService->getPurchasedBundlesData($user));

        // Purchased Product Data
        $data = array_merge($data, $userPurchaseHistoryService->getPurchasedProductsData($user));

        if (auth()->user()->can('admin_forum_topics_lists')) {
            $data['topics'] = ForumTopic::where('creator_id', $user->id)
                ->with([
                    'posts' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'forum'
                ])
                ->withCount('posts')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.users.edit', $data);
    }

   /* private function getBecomeInstructorFormFieldValues($becomeInstructor)
    {
        $values = [];
        $becomeInstructorFields = \App\Models\UserFormField::query()->where('become_instructor_id', $becomeInstructor->id)->get();

        foreach ($becomeInstructorFields as $becomeInstructorField) {
            $field = $becomeInstructorField->field;

            $values[$field->title] = $field->getFieldValueTitle($becomeInstructorField->value);
        }

        return $values;
    }*/



    public function update(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $this->validate($request, [
            'full_name' => 'required|min:3|max:255',
            'email' => (!empty($user->email)) ? 'required|email|unique:users,email,' . $user->id . ',id,deleted_at,NULL' : 'nullable|email|unique:users',
            'mobile' => (!empty($user->mobile)) ? 'required|numeric|unique:users,mobile,' . $user->id . ',id,deleted_at,NULL' : 'nullable|numeric|unique:users',
            'username' => 'required|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string',
            'bio' => 'nullable|string|min:3|max:48',
            'about' => 'nullable|string|min:3',
            'certificate_additional' => 'nullable|string|max:255',
            'status' => 'required|' . Rule::in(User::$statuses),
            'ban_start_at' => 'required_if:ban,on',
            'ban_end_at' => 'required_if:ban,on',
        ]);

        $data = $request->all();

        $userManagementService = new UserManagementService();
        $result = $userManagementService->updateUser($user, $data);

        if ($result === false) {
            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => 'Selected role not exist',
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        return redirect()->back();
    }

    public function updateImage(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $user->avatar = $request->get('avatar', null);

        if (!empty($request->get('cover_img', null))) {
            $user->cover_img = $request->get('cover_img', null);
        }

        $user->save();

        return redirect()->back();
    }

    public function updateFormFields(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $userType = "organization";
        if ($user->isTeacher()) {
            $userType = "teacher";
        } elseif ($user->isUser()) {
            $userType = "user";
        }

        $form = $this->getFormFieldsByType($userType);

        if (!empty($form)) {
            $errors = $this->checkFormRequiredFields($request, $form);

            if (count($errors)) {
                return redirect()->back()->withErrors($errors);
            }

            $this->storeFormFields($request->all(), $user);
        }

        return redirect()->back();
    }

    public function financialUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);
        $data = $request->all();

        $user->update([
            'identity_scan' => $data['identity_scan'] ?? null,
            'certificate' => $data['certificate'] ?? null,
            'address' => $data['address'],
            'financial_approval' => (!empty($data['financial_approval']) and $data['financial_approval'] == 'on'),
            'installment_approval' => (!empty($data['installment_approval']) and $data['installment_approval'] == 'on'),
            'enable_installments' => (!empty($data['enable_installments']) and $data['enable_installments'] == 'on'),
            'disable_cashback' => (!empty($data['disable_cashback']) and $data['disable_cashback'] == 'on'),
            'enable_registration_bonus' => (!empty($data['enable_registration_bonus']) and $data['enable_registration_bonus'] == 'on'),
            'registration_bonus_amount' => !empty($data['registration_bonus_amount']) ? $data['registration_bonus_amount'] : null,
        ]);

        $userCommissionService = new UserCommissionService();
        $userCommissionService->storeUserCommissions($user, $data);

        if (!empty($data['bank_id'])) {
            UserSelectedBank::query()->where('user_id', $user->id)->delete();

            $userSelectedBank = UserSelectedBank::query()->create([
                'user_id' => $user->id,
                'user_bank_id' => $data['bank_id']
            ]);

            if (!empty($data['bank_specifications'])) {
                $specificationInsert = [];

                foreach ($data['bank_specifications'] as $specificationId => $specificationValue) {
                    if (!empty($specificationValue)) {
                        $specificationInsert[] = [
                            'user_selected_bank_id' => $userSelectedBank->id,
                            'user_bank_specification_id' => $specificationId,
                            'value' => $specificationValue
                        ];
                    }
                }

                UserSelectedBankSpecification::query()->insert($specificationInsert);
            }
        }

        return redirect()->back();
    }



    public function occupationsUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);
        $data = $request->all();

        UserOccupation::where('user_id', $user->id)->delete();
        if (!empty($data['occupations'])) {

            foreach ($data['occupations'] as $category_id) {
                UserOccupation::create([
                    'user_id' => $user->id,
                    'category_id' => $category_id
                ]);
            }
        }

        return redirect()->back();
    }

    public function badgesUpdate(Request $request, $id)
    {
        $this->authorize('admin_users_edit');

        $this->validate($request, [
            'badge_id' => 'required'
        ]);

        $data = $request->all();
        $user = User::findOrFail($id);
        $badge = Badge::findOrFail($data['badge_id']);

        UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'created_at' => time()
        ]);

        sendNotification('new_badge', ['[u.b.title]' => $badge->title], $user->id);

        return redirect()->back();
    }

    public function deleteBadge(Request $request, $id, $badge_id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $badge = UserBadge::where('id', $badge_id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($badge)) {
            $badge->delete();
        }

        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_users_delete');

        $user = User::find($id);

        if ($user) {
            $user->delete();
        }

        return redirect()->back();
    }

    public function acceptRequestToInstructor($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($id);

        $becomeInstructors = BecomeInstructor::where('user_id', $user->id)->first();

        if (!empty($becomeInstructors)) {
            $role = Role::where('name', $becomeInstructors->role)->first();

            if (!empty($role)) {
                $user->update([
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                ]);

                $becomeInstructors->update([
                    'status' => 'accept'
                ]);

                // Send Notification
                $becomeInstructors->sendNotificationToUser('accept');
            }

            return redirect(getAdminPanelUrl() . '/users/' . $user->id . '/edit')->with(['msg' => trans('admin/pages/users.user_role_updated')]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $option = $request->get('option');

        $users = User::select('id', 'full_name as name')
            //->where('role_name', Role::$user)
            ->where(function ($query) use ($term) {
                $query->where('full_name', 'like', '%' . $term . '%');
            });

        if ($option === "for_user_group") {
            $users->whereNotIn('id', GroupUser::all()->pluck('user_id'));
        }

        if ($option === "just_teacher_role") {
            $users->where('role_name', Role::$teacher);
        }

        if ($option === "just_student_role") {
            $users->where('role_name', Role::$user);
        }

        if ($option === "just_organization_role") {
            $users->where('role_name', Role::$organization);
        }

        if ($option === "just_organization_and_teacher_role") {
            $users->whereIn('role_name', [Role::$organization, Role::$teacher]);
        }

        if ($option === "except_user") {
            $users->where('role_name', '!=', Role::$user);
        }

        if ($option === "consultants") {
            $users->whereHas('meeting', function ($query) {
                $query->where('disabled', false)
                    ->whereHas('meetingTimes');
            });
        }

        return response()->json($users->get(), 200);
    }

    public function impersonate($user_id)
    {
        $this->authorize('admin_users_impersonate');

        $user = User::findOrFail($user_id);

        if ($user->isAdmin()) {
            return redirect(getAdminPanelUrl() . '');
        }

        session()->put(['impersonated' => $user->id]);

        return redirect('/panel');
    }

    public function exportExcelOrganizations(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->organizations($request, true);

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'organizations',
            'status' => Export::STATUS_PENDING,
        ]);

        ProcessGenericExport::dispatch(OrganizationsExport::class, $users, $exportRecord->id, 'organizations');

        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }

    public function exportExcelInstructors(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->instructors($request, true);

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'instructors',
            'status' => Export::STATUS_PENDING,
        ]);
        ProcessGenericExport::dispatch(InstructorsExport::class, $users, $exportRecord->id, 'instructors');
        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }

    public function exportExcelStudents(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->students($request, true);

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'students',
            'status' => Export::STATUS_PENDING,
        ]);
        ProcessGenericExport::dispatch(StudentsExport::class, $users, $exportRecord->id, 'students');
        return redirect()->back()->with('toast', ['status' => 'success', 'title' => 'Export', 'msg' => 'Your export is processing. You will get a notification when it is ready to download.']);
    }

    public function allUsers(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::query();

        $totalUsers = deepClone($query)->count();
        $activeUsers = deepClone($query)->where('status', 'active')->count();
        $inactiveUsers = deepClone($query)->where('status', 'inactive')->count();
        $banUsers = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $roles = Role::all();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $userFilterService = new UserFilterService();
        $query = $userFilterService->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('users.created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('users.created_at', 'desc')
                ->paginate(10);
        }

        $userEnrichmentService = new UserEnrichmentService();
        $users = $userEnrichmentService->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'pageTitle' => trans('admin/main.users_list'),
            'users' => $users,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'banUsers' => $banUsers,
            'userGroups' => $userGroups,
            'roles' => $roles,
            'organizations' => $organizations,
        ];

        return view('admin.users.all_users', $data);
    }

    public function exportExcelAllUsers(Request $request)
    {
        $this->authorize('admin_users_export_excel');

        $users = $this->allUsers($request, true);

        $exportRecord = Export::create([
            'user_id' => auth()->id(),
            'type' => 'users',
            'status' => Export::STATUS_PENDING,
        ]);

        ProcessUserExport::dispatch($users, $exportRecord->id);

        return back()->with('success', trans('admin/main.export_queued_message'));
    }

    public function userRegistrationPackage(Request $request, $id)
    {
        $this->authorize('admin_update_user_registration_package');

        $this->validate($request, [
            'instructors_count' => 'nullable|numeric',
            'students_count' => 'nullable|numeric',
            'courses_capacity' => 'nullable|numeric',
            'courses_count' => 'nullable|numeric',
            'meeting_count' => 'nullable|numeric',
            'product_count' => 'nullable|numeric',
            'events_count' => 'nullable|numeric',
            'meeting_packages_count' => 'nullable|numeric',
        ]);

        $user = User::findOrFail($id);

        if ($user->isOrganization() or $user->isTeacher()) {
            $data = $request->all();

            UserRegistrationPackage::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'instructors_count' => $data['instructors_count'] ?? null,
                'students_count' => $data['students_count'] ?? null,
                'courses_capacity' => $data['courses_capacity'] ?? null,
                'courses_count' => $data['courses_count'] ?? null,
                'meeting_count' => $data['meeting_count'] ?? null,
                'product_count' => $data['product_count'] ?? null,
                'events_count' => $data['events_count'] ?? null,
                'meeting_packages_count' => $data['meeting_packages_count'] ?? null,
                'status' => $data['status'],
                'created_at' => time(),
            ]);

            return redirect()->back();
        }

        abort(404);
    }

    public function meetingSettings(Request $request, $id)
    {
        $this->authorize('admin_update_user_meeting_settings');

        $user = User::findOrFail($id);

        if ($user->isOrganization() or $user->isTeacher()) {
            $data = $request->all();

            $user->update([
                "level_of_training" => !empty($data['level_of_training']) ? (new UserLevelOfTraining())->getValue($data['level_of_training']) : null,
                "meeting_type" => $data['meeting_type'] ?? null,
                "group_meeting" => (!empty($data['group_meeting']) and $data['group_meeting'] == 'on'),
                "country_id" => $data['country_id'] ?? null,
                "province_id" => $data['province_id'] ?? null,
                "city_id" => $data['city_id'] ?? null,
                "district_id" => $data['district_id'] ?? null,
                "location" => (!empty($data['latitude']) and !empty($data['longitude'])) ? DB::raw("POINT(" . $data['latitude'] . "," . $data['longitude'] . ")") : null,
            ]);

            $updateUserMeta = [
                "gender" => $data['gender'] ?? null,
                "age" => $data['age'] ?? null,
                "address" => $data['address'] ?? null,
            ];

            foreach ($updateUserMeta as $name => $value) {
                $checkMeta = UserMeta::where('user_id', $user->id)
                    ->where('name', $name)
                    ->first();

                if (!empty($checkMeta)) {
                    if (!empty($value)) {
                        $checkMeta->update([
                            'value' => $value
                        ]);
                    } else {
                        $checkMeta->delete();
                    }
                } else if (!empty($value)) {
                    UserMeta::create([
                        'user_id' => $user->id,
                        'name' => $name,
                        'value' => $value
                    ]);
                }
            }

            return redirect()->back();
        }

        abort(404);
    }

    public function disableCashbackToggle($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'disable_cashback' => !$user->disable_cashback
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.cashback_was_disabled_for_the_user'),
            'status' => 'success'
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function disableRegitrationBonusStatus($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'enable_registration_bonus' => false
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.registration_bonus_was_disabled_for_the_user'),
            'status' => 'success'
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function disableInstallmentApproval($id)
    {
        $this->authorize('admin_users_edit');

        $user = User::query()->findOrFail($id);

        $user->update([
            'installment_approval' => false
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.installment_was_disabled_for_the_user'),
            'status' => 'success'
        ];

        return back()->with(['toast' => $toastData]);
    }
}
