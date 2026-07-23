<?php

namespace App\Services\App;

use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Models\Tag;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;

class WebinarCreationService
{
    use VideoDemoTrait;

    public function createWebinar($request, $data)
    {
        if (!empty($data['capacity']) and !empty($data['sales_count_number']) and $data['sales_count_number'] > $data['capacity']) {
            return [
                'error' => true,
                'field' => 'sales_count_number',
                'message' => trans('validation.digits_between', [
                    'attribute' => trans('update.sales_count_number'),
                    'min' => 0,
                    'max' => $data['capacity']
                ])
            ];
        }

        if ($data['type'] != Webinar::$webinar) {
            $data['start_date'] = null;
        }

        if (!empty($data['start_date']) and $data['type'] == Webinar::$webinar) {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

            $data['start_date'] = $startDate->getTimestamp();
        }

        if (empty($data['slug'])) {
            $data['slug'] = Webinar::makeSlug($data['title']);
        }

        $data = $this->handleVideoDemoData($request, $data['teacher_id'], $data, "course_demo_" . time());

        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

        $webinar = Webinar::create([
            'type' => $data['type'],
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'icon' => $data['icon'] ?? null,
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'sales_count_number' => $data['sales_count_number'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'start_date' => (!empty($data['start_date'])) ? $data['start_date'] : null,
            'timezone' => $data['timezone'] ?? null,
            'duration' => $data['duration'] ?? null,
            'support' => (!empty($data['support']) and $data['support'] == "on"),
            'certificate' => (!empty($data['certificate']) and $data['certificate'] == "on"),
            'downloadable' => (!empty($data['downloadable']) and $data['downloadable'] == "on"),
            'partner_instructor' => (!empty($data['partner_instructor']) and $data['partner_instructor'] == "on"),
            'subscribe' => (!empty($data['subscribe']) and $data['subscribe'] == "on"),
            'private' => (!empty($data['private']) and $data['private'] == "on"),
            'only_for_students' => (!empty($data['only_for_students']) and $data['only_for_students'] == "on"),
            'forum' => (!empty($data['forum']) and $data['forum'] == "on"),
            'enable_waitlist' => (!empty($data['enable_waitlist']) and $data['enable_waitlist'] == "on"),
            'access_days' => $data['access_days'] ?? null,
            'price' => $data['price'],
            'organization_price' => $data['organization_price'] ?? null,
            'points' => $data['points'] ?? null,
            'category_id' => $data['category_id'],
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => Webinar::$pending,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        if ($webinar) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
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

        return getAdminPanelUrl() . '/webinars/' . $webinar->id . '/edit?locale=' . $data['locale'];
    }
}
