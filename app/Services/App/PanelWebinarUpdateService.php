<?php

namespace App\Services\App;

use App\Http\Controllers\Panel\WebinarExtraDescriptionController;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Tag;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Validator;

class PanelWebinarUpdateService
{
    protected $panelWebinarCreateService;

    public function __construct(PanelWebinarCreateService $panelWebinarCreateService)
    {
        $this->panelWebinarCreateService = $panelWebinarCreateService;
    }

    public function edit(Request $request, $id, $step = 1)
    {
        Gate::authorize("panel_webinars_create");
        $user = auth()->user();
        $isOrganization = $user->isOrganization();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }
        $locale = $request->get('locale', app()->getLocale());
        $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;
        if ($step > $stepCount) {
            return redirect("/panel/courses/{$id}/step/{$stepCount}");
        }
        $data = [
            'pageTitle' => trans('webinars.new_page_title_step', ['step' => $step]),
            'currentStep' => $step,
            'isOrganization' => $isOrganization,
            'userLanguages' => getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
            'stepCount' => $stepCount
        ];
        $query = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });
                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                });
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
            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();
            $data['categories'] = $categories;
        } elseif ($step == 3) {
            $query->with([
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ]);
        } elseif ($step == 4) {
            $query->with([
                'chapters' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                            $query->with([
                                'quiz' => function ($query) {
                                    $query->with([
                                        'quizQuestions' => function ($query) {
                                            $query->orderBy('order', 'asc');
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                },
            ]);
        } elseif ($step == 5) {
            $query->with([
                'prerequisites' => function ($query) {
                    $query->with(['course' => function ($qu) {
                        $qu->with(['teacher' => function ($q) {
                            $q->select('id', 'full_name');
                        }]);
                    }])->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 6) {
            $query->with([
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'webinarExtraDescription' => function ($query) {
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
        $data['pageTitle'] = trans('public.edit') . ' ' . $webinar->title;
        $definedLanguage = [];
        if ($webinar->translations) {
            $definedLanguage = $webinar->translations->pluck('locale')->toArray();
        }
        $data['definedLanguage'] = $definedLanguage;
        if ($step == 2) {
            $data['webinarTags'] = $webinar->tags->pluck('title')->toArray();
            $webinarCategoryFilters = !empty($webinar->category) ? $webinar->category->filters : [];
            if (empty($webinar->category) and !empty($request->old('category_id'))) {
                $category = Category::where('id', $request->old('category_id'))->first();
                if (!empty($category)) {
                    $webinarCategoryFilters = $category->filters;
                }
            }
            $data['webinarCategoryFilters'] = $webinarCategoryFilters;
        }
        if ($step == 3) {
            $data['sumTicketsCapacities'] = $webinar->tickets->sum('capacity');
        }
        return view('design_1.panel.webinars.create.index', $data);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize("panel_webinars_create");
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }
        $rules = [];
        $data = $request->all();
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = (!empty($data['get_next']) and $data['get_next'] == 1);
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });
                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            })->first();
        if (empty($webinar)) {
            abort(404);
        }
        if ($currentStep == 1) {
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'summary' => 'required',
                'description' => 'required',
            ];
        }
        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required',
                'duration' => 'required|numeric',
                'partners' => 'required_if:partner_instructor,on',
                'capacity' => 'nullable|numeric|min:0'
            ];
            if ($webinar->isWebinar()) {
                $rules['start_date'] = 'required|date';
            }
        }
        if ($currentStep == 3) {
            $rules = [
                'price' => 'nullable|numeric|min:0',
            ];
        }
        $webinarRulesRequired = false;
        $directPublicationOfCourses = !empty(getGeneralOptionsSettings('direct_publication_of_courses'));
        if (!$directPublicationOfCourses and (($currentStep == 8 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft))) {
            $webinarRulesRequired = empty($data['rules']);
        }
        Validator::make($request->all(), $rules)->validate();
        $status = ($isDraft or $webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;
        if ($directPublicationOfCourses and !$getNextStep and !$isDraft) {
            $status = Webinar::$active;
        }
        $data['status'] = $status;
        $data['updated_at'] = time();
        if ($currentStep == 1) {
            $data['private'] = (!empty($data['private']) and $data['private'] == 'on');
            // Handle Image and Video
            $webinar = $this->panelWebinarCreateService->storeWebinarMedia($request, $webinar);
            unset(
                $data['thumbnail'],
                $data['image_cover'],
                $data['icon'],
                $data['video_demo_source'],
                $data['demo_video_path'],
                $data['demo_video_local'],
            );
        }
        if ($currentStep == 2) {
            // Check Capacity
            $userPackage = new UserPackage($webinar->creator);
            $userCoursesCapacityLimited = $userPackage->checkPackageLimit('courses_capacity', $data['capacity']);
            if ($userCoursesCapacityLimited) {
                session()->put('registration_package_limited', $userCoursesCapacityLimited);
                return redirect()->back()->withInput($data);
            }
            // .\ Check Capacity
            if ($webinar->isWebinar()) {
                if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                    $data['timezone'] = getTimezone();
                }
                $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);
                $data['start_date'] = $startDate->getTimestamp();
            }
            $data['forum'] = !empty($data['forum']) ? true : false;
            $data['support'] = !empty($data['support']) ? true : false;
            $data['certificate'] = !empty($data['certificate']) ? true : false;
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
            $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
            $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;
        }
        if ($currentStep == 6) {
            $webinarExtraDescriptionController = (new WebinarExtraDescriptionController());
            $webinarExtraDescriptionController->storeCompanyLogos($request, 'webinar_id', $webinar->id, 'webinars');
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
        if ($webinar and $currentStep == 1) {
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
        unset($data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['title'],
            $data['description'],
            $data['seo_description'],
            $data['companyLogos'],
        );
        if (empty($data['teacher_id']) and $user->isOrganization() and $webinar->creator_id == $user->id) {
            $data['teacher_id'] = $user->id;
        }
        $webinar->update($data);
        $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;
        $url = '/panel/courses';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;
            $url = '/panel/courses/' . $webinar->id . '/step/' . (($nextStep <= $stepCount) ? $nextStep : $stepCount);
        }
        if ($webinarRulesRequired) {
            $url = '/panel/courses/' . $webinar->id . '/step/8';
            return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
        }
        if ($status != Webinar::$active and !$getNextStep and !$isDraft and !$webinarRulesRequired) {
            sendNotification('course_created', ['[c.title]' => $webinar->title], $user->id);
            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[item_title]' => $webinar->title,
                '[content_type]' => trans('admin/main.course'),
            ];
            sendNotification("content_review_request", $notifyOptions, 1);
        }
        return redirect($url);
    }
}
