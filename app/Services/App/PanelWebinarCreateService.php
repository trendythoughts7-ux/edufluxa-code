<?php

namespace App\Services\App;

use App\Enums\UploadSource;
use App\Http\Controllers\MainTraits\FilesTraits;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Category;
use App\Models\Role;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PanelWebinarCreateService
{
    use FilesTraits;

    public function create(Request $request)
    {
        Gate::authorize("panel_webinars_create");

        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $userPackage = new UserPackage();
        $userCoursesCountLimited = $userPackage->checkPackageLimit('courses_count');

        if ($userCoursesCountLimited) {
            session()->put('registration_package_limited', $userCoursesCountLimited);

            return redirect()->back();
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $teachers = null;
        $isOrganization = $user->isOrganization();

        if ($isOrganization) {
            $teachers = User::where('role_name', Role::$teacher)
                ->where('organ_id', $user->id)->get();
        }

        $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;

        $data = [
            'pageTitle' => trans('webinars.new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories,
            'isOrganization' => $isOrganization,
            'currentStep' => 1,
            'stepCount' => $stepCount,
            'userLanguages' => getUserLanguagesLists(),
        ];

        return view('design_1.panel.webinars.create.index', $data);
    }

    public function store(Request $request)
    {
        Gate::authorize("panel_webinars_create");

        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $userPackage = new UserPackage();
        $userCoursesCountLimited = $userPackage->checkPackageLimit('courses_count');

        if ($userCoursesCountLimited) {
            session()->put('registration_package_limited', $userCoursesCountLimited);

            return redirect()->back();
        }

        $currentStep = $request->get('current_step', 1);

        $rules = [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'summary' => 'required',
            'description' => 'required',
        ];

        Validator::make($request->all(), $rules)->validate();

        $data = $request->all();

        $webinar = Webinar::create([
            'teacher_id' => $user->isTeacher() ? $user->id : (!empty($data['teacher_id']) ? $data['teacher_id'] : $user->id),
            'creator_id' => $user->id,
            'slug' => Webinar::makeSlug($data['title']),
            'type' => $data['type'],
            'private' => (!empty($data['private']) and $data['private'] == 'on'),
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Webinar::$isDraft : Webinar::$pending,
            'created_at' => time(),
        ]);

        if ($webinar) {
            $this->storeWebinarMedia($request, $webinar);

            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[item_title]' => $webinar->title,
            '[content_type]' => trans('admin/main.course'),
        ];
        sendNotification("new_item_created", $notifyOptions, 1);

        $url = '/panel/courses';
        if ($data['get_next'] == 1) {
            $url = '/panel/courses/' . $webinar->id . '/step/2';
        }

        return redirect($url);
    }

    protected function storeWebinarMedia(Request $request, $webinar)
    {
        $thumbnail = $webinar->thumbnail ?? null;
        $imageCover = $webinar->image_cover ?? null;
        $icon = $webinar->icon ?? null;
        $videoDemoSource = $webinar->video_demo_source ?? null;
        $videoDemo = $webinar->video_demo ?? null;

        if (!empty($request->file('thumbnail'))) {
            $thumbnail = $this->uploadFile($request->file('thumbnail'), "webinars/{$webinar->id}", 'thumbnail', $webinar->creator_id);
        }

        if (!empty($request->file('image_cover'))) {
            $imageCover = $this->uploadFile($request->file('image_cover'), "webinars/{$webinar->id}", 'image_cover', $webinar->creator_id);
        }

        if (!empty($request->file('icon'))) {
            $icon = $this->uploadFile($request->file('icon'), "webinars/{$webinar->id}", 'icon', $webinar->creator_id);
        }

        if (in_array($request->get('video_demo_source'), UploadSource::urlPathItems) and !empty($request->get('demo_video_path'))) {
            $videoDemoSource = $request->get('video_demo_source');
            $videoDemo = $request->get('demo_video_path');
        } elseif ($request->get('video_demo_source') == UploadSource::UPLOAD and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::UPLOAD;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "webinars/{$webinar->id}", 'video', $webinar->creator_id);
        } elseif ($request->get('video_demo_source') == UploadSource::S3 and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::S3;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "webinars/{$webinar->id}", 'video', $webinar->creator_id, 'minio');
        } elseif ($request->get('video_demo_source') == UploadSource::SECURE_HOST and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::SECURE_HOST;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "webinars/{$webinar->id}", "course_{$webinar->id}_video_demo", $webinar->creator_id, 'bunny');
        }

        $webinar->update([
            'thumbnail' => $thumbnail,
            'image_cover' => $imageCover,
            'video_demo_source' => $videoDemoSource,
            'video_demo' => $videoDemo,
            'icon' => $icon,
        ]);

        return $webinar;
    }
}
