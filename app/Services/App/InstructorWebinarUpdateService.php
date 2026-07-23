<?php

namespace App\Services\App;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use Illuminate\Http\Request;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Validator;

class InstructorWebinarUpdateService
{
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $rules = [];
        $data = $request->all();
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = !empty($data['get_next'] and $data['get_next'] == 1) ? true : false;
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (empty($webinar)) {
            abort(404);
        }

        if ($currentStep == 1) {
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
            ];
        }

        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required|exists:categories,id',
                'duration' => 'required|max:10',
            ];

            if ($webinar->isWebinar()) {
                $rules['start_date'] = 'required|date';
                $rules['capacity'] = 'required|numeric|max:10';
            }
        }

        $webinarRulesRequired = false;
        if (($currentStep == 8 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft)) {
            $webinarRulesRequired = empty($data['rules']);
        }

        validateParam($request->all(), $rules);

        $data['status'] = ($isDraft or $webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;
        $data['updated_at'] = time();

        if ($currentStep == 1) {
            $data['private'] = (!empty($data['private']) and $data['private'] == 'on');
            $data['slug'] = SlugService::createSlug($webinar, 'slug', $data['title']); // bug fix: avoid $webinar->slug=null (Sluggable's auto-regen reads title via Translatable mutator, leaking it into raw attributes -> SQL error)
        }

        if ($currentStep == 2) {
            if ($webinar->type == 'webinar') {
                $data['start_date'] = strtotime($data['start_date']);
            }

            $data['support'] = (!empty($data['support']) && $data['support'] == 1) ? true : false;
            $data['downloadable'] = (!empty($data['downloadable']) && $data['downloadable'] == 1) ? true : false;
            $data['partner_instructor'] = (!empty($data['partner_instructor']) && $data['partner_instructor'] == 1) ? true : false;

            if (empty($data['partner_instructor']) || $data['partner_instructor'] == 0) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                unset($data['partners']);
            }

            if ($data['category_id'] !== $webinar->category_id) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            }
        }

        if ($currentStep == 3) {
            $data['subscribe'] = (!empty($data['subscribe']) && $data['subscribe'] == 1) ? true : false;
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
            $tags = explode(',', $request->get('tags'));
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

        $url = '/panel/courses';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/courses/' . $webinar->id . '/step/' . (($nextStep <= 8) ? $nextStep : 8);
        }

        if ($webinarRulesRequired) {
            $url = '/panel/courses/' . $webinar->id . '/step/8';

            return ['redirect' => $url, 'withErrors' => ['rules' => trans('validation.required', ['attribute' => 'rules'])]];
        }

        if (!$getNextStep and !$isDraft and !$webinarRulesRequired) {
            sendNotification('course_created', ['[c.title]' => $webinar->title], $user->id);
        }

        return ['redirect' => $url];
    }

    public function edit($id, $step = 1)
    {
        $user = auth()->user();
        $isOrganization = $user->isOrganization();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $data = [
            'pageTitle' => trans('webinars.new_page_title_step', ['step' => $step]),
            'currentStep' => $step,
            'isOrganization' => $isOrganization,
        ];

        $query = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            });

        if ($step == '1') {
            $data['teachers'] = $user->getOrganizationTeachers()->get();
        } elseif ($step == 2) {
            $query->with([
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'filterOptions',
                'webinarPartnerTeacher' => function ($query) {
                    $query->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }]);
                },
                'tags',
            ]);

            $categories = Category::getCategories();

            $data['categories'] = $categories;
        } elseif ($step == 3) {
            $query->with([
                'tickets' => function ($query) {
                    $query->orderBy('order', 'desc');
                },
            ]);
        }
 elseif ($step == 4) {
            $query->with([
                'chapters' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with([
                        'files' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'sessions' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'textLessons' => function ($query) {
                            $query->with(['attachments' => function ($qu) {
                                $qu->with('file');
                            }])->orderBy('order', 'asc');
                        }
                    ]);
                },
            ]);
        } elseif ($step == 5) {
            $query->with([
                'prerequisites' => function ($query) {
                    $query->with(['course' => function ($qu) {
                        $qu->select('id', 'title', 'teacher_id')
                            ->with(['teacher' => function ($q) {
                                $q->select('id', 'full_name');
                            }]);
                    }])->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 6) {
            $query->with([
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 7) {
            $query->with([
                'quizzes',
                'chapters' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc');
                }
            ]);

            $teacherQuizzes = Quiz::where('webinar_id', null)
                ->where('creator_id', $user->id)
                ->whereNull('webinar_id')
                ->get();

            $data['teacherQuizzes'] = $teacherQuizzes;
        }


        $webinar = $query->first();

        if (empty($webinar)) {
            abort(404);
        }

        $data['webinar'] = $webinar;


        if ($step == 2) {
            $data['webinarTags'] = $webinar->tags->pluck('title')->toArray();
        }

        if ($step == 3) {
            $data['sumTicketsCapacities'] = $webinar->tickets->sum('capacity');
        }

        return $data;
    }

    public function updateAll(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $rules = [];
        $data = $request->all();
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = !empty($data['get_next'] and $data['get_next'] == 1) ? true : false;
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (empty($webinar)) {
            abort(404);
        }

        if ($currentStep == 1) {
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
            ];
        }

        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required',
                'duration' => 'required',
            ];

            if ($webinar->isWebinar()) {
                $rules['start_date'] = 'required|date';
                $rules['capacity'] = 'required|integer';
            }
        }

        $webinarRulesRequired = false;
        if (($currentStep == 8 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft)) {
            $webinarRulesRequired = empty($data['rules']);
        }

        Validator::make($request->all(), $rules)->validate();


        $data['status'] = ($isDraft or $webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;
        $data['updated_at'] = time();

        if ($currentStep == 1) {
            $data['private'] = (!empty($data['private']) and $data['private'] == 'on');
            $data['slug'] = SlugService::createSlug($webinar, 'slug', $data['title']); // bug fix: avoid $webinar->slug=null (Sluggable's auto-regen reads title via Translatable mutator, leaking it into raw attributes -> SQL error)
        }

        if ($currentStep == 2) {
            if ($webinar->type == 'webinar') {
                $data['start_date'] = strtotime($data['start_date']);
            }

            $data['support'] = !empty($data['support']) ? true : false;
            $data['downloadable'] = !empty($data['downloadable']) ? true : false;
            $data['partner_instructor'] = !empty($data['partner_instructor']) ? true : false;

            if (empty($data['partner_instructor'])) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                unset($data['partners']);
            }

            if ($data['category_id'] !== $webinar->category_id) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            }
        }

        if ($currentStep == 3) {
            $data['subscribe'] = !empty($data['subscribe']) ? true : false;
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
            $tags = explode(',', $request->get('tags'));
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

        $url = '/panel/courses';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/courses/' . $webinar->id . '/step/' . (($nextStep <= 8) ? $nextStep : 8);
        }

        if ($webinarRulesRequired) {
            $url = '/panel/courses/' . $webinar->id . '/step/8';

            return ['redirect' => $url, 'withErrors' => ['rules' => trans('validation.required', ['attribute' => 'rules'])]];
        }

        if (!$getNextStep and !$isDraft and !$webinarRulesRequired) {
            sendNotification('course_created', ['[c.title]' => $webinar->title], $user->id);
        }

        return ['redirect' => $url];
    }
}
