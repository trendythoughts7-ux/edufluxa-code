<?php

namespace App\Services\App;

use App\Models\Category;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;
use Illuminate\Http\Request;

class InstructorWebinarCreationService
{
    public function create(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $categories = Category::getCategories();

        $teachers = null;
        $isOrganization = $user->isOrganization();

        if ($isOrganization) {
            $teachers = User::where('role_name', Role::$teacher)
                ->where('organ_id', $user->id)->get();
        }

        $data = [
            'pageTitle' => trans('webinars.new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories,
            'isOrganization' => $isOrganization,
            'currentStep' => 1,
        ];

        return $data;
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $currentStep = $request->get('current_step', 1);

        $rules = [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
        ];

        if (!$user->isTeacher()) {
            $rules['teacher_id'] = 'required|exists:users,id';
        }
        validateParam($request->all(), $rules);

        $data = $request->all();

        $webinar = Webinar::create([
            'teacher_id' => $user->isTeacher() ? $user->id : $data['teacher_id'],
            'creator_id' => $user->id,
            'type' => $data['type'],
            'private' => (!empty($data['private']) and $data['private'] == 1) ? true : false,
            'title' => $data['title'],

            'slug' => $data['title'],

            'seo_description' => $data['seo_description'] ?? null,
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'] ?? null,
            'description' => $data['description'],
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Webinar::$isDraft : Webinar::$pending,
            'created_at' => time(),
        ]);
        return apiResponse2(1, 'stored', trans('public.stored'));

    }

    public function storeAll(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $currentStep = $request->get('current_step', 1);

        $rules = [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',

            'duration' => 'required|numeric|max:10',
            'start_date' => 'required_if:type,webinar|date',
            'capacity' => 'required_if:type,webinar|numeric|max:10',

            'rules' => 'required|in:1',
            'is_draft' => 'boolean|in:0,1',
            'private' => 'boolean|in:0,1',
            'support' => 'boolean|in:0,1',
            'downloadable' => 'boolean|in:0,1',
            'partner_instructor' => 'boolean|in:0,1',
            'subscribe' => 'boolean|in:0,1',
            'tags' => 'array',

            // 'filters'
            // partners


        ];

        if (!$user->isTeacher()) {
            $rules['teacher_id'] = 'required|exists:users,id';
        }
        validateParam($request->all(), $rules);

        $data = $request->all();

        $rules = [];
        $data = $request->all();
        $webinar_type = $data['type'];
        $webinar = Webinar::create([
            'teacher_id' => $user->isTeacher() ? $user->id : $data['teacher_id'],
            'creator_id' => $user->id,
            'type' => $data['type'],
            'private' => (!empty($data['private']) and $data['private'] == 1) ? true : false,
            'title' => $data['title'],

            'slug' => $data['title'],

            'seo_description' => $data['seo_description'] ?? null,
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'] ?? null,
            'description' => $data['description'],
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Webinar::$isDraft : Webinar::$pending,
            'created_at' => time(),
        ]);

        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $webinarRulesRequired = (!empty($data['rules']) && $data['rules'] == 1);

        $data['status'] = ($isDraft or !$webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;
        $data['private'] = (!empty($data['private']) and $data['private'] == 1);

        if ($webinar_type == 'webinar') {
            $data['start_date'] = strtotime($data['start_date']);
        }
        $data['support'] = (!empty($data['support']) && $data['support'] == 1) ? true : false;
        $data['downloadable'] = (!empty($data['downloadable']) && $data['downloadable'] == 1) ? true : false;
        $data['partner_instructor'] = (!empty($data['partner_instructor']) && $data['partner_instructor'] == 1) ? true : false;
        $data['subscribe'] = (!empty($data['subscribe']) && $data['subscribe'] == 1) ? true : false;

        if (empty($data['partner_instructor']) || $data['partner_instructor'] == 0) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
            unset($data['partners']);
        }
        if ($data['category_id'] !== $webinar->category_id) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            foreach ($filters as $filter) {
                WebinarFilterOption::create([
                    'webinar_id' => $webinar->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = $request->get('tags');
            //  $tags = explode(',', $request->get('tags'));
            Tag::where('webinar_id', $webinar->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'webinar_id' => $webinar->id,
                    'title' => $tag,
                ]);
            }
        }

        if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

            foreach ($request->get('partners') as $partnerId) {
                WebinarPartnerTeacher::create([
                    'webinar_id' => $webinar->id,
                    'teacher_id' => $partnerId,
                ]);
            }
        }

        unset($data['_token'], $data['current_step'], $data['draft'], $data['get_next'], $data['partners'], $data['tags'], $data['filters'], $data['ajax']);

        $webinar->update($data);


        return apiResponse2(1, 'stored', trans('public.stored'));

    }
}
