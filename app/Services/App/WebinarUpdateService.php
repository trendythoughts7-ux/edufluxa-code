<?php

namespace App\Services\App;

use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Http\Controllers\Admin\traits\WebinarChangeCreator;
use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Tag;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Models\WebinarFilterOption;
use App\Models\WebinarPartnerTeacher;
use App\User;

class WebinarUpdateService
{
    use VideoDemoTrait;
    use ProductBadgeTrait;
    use WebinarChangeCreator;

    public function checkTeacherOwnership($data, $webinar)
    {
        if (!empty($data['teacher_id'])) {
            $teacher = User::find($data['teacher_id']);
            $creator = !empty($data['organ_id']) ? User::find($data['organ_id']) : $webinar->creator;

            if (empty($teacher) or ($creator->isOrganization() and ($teacher->organ_id != $creator->id and $teacher->id != $creator->id))) {
                return [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                    'status' => 'error'
                ];
            }
        }

        return true;
    }

    public function updateWebinar($request, $webinar, $data)
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

        if (empty($data['slug'])) {
            $data['slug'] = Webinar::makeSlug($data['title']);
        }

        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
        $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

        $data['status'] = $publish ? Webinar::$active : ($reject ? Webinar::$inactive : ($isDraft ? Webinar::$isDraft : Webinar::$pending));
        $data['updated_at'] = time();

        if (!empty($data['start_date']) and $webinar->type == 'webinar') {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

            $data['start_date'] = $startDate->getTimestamp();
        } else {
            $data['start_date'] = null;
        }

        $data['support'] = (!empty($data['support']) and $data['support'] == "on");
        $data['certificate'] = (!empty($data['certificate']) and $data['certificate'] == "on");
        $data['downloadable'] = (!empty($data['downloadable']) and $data['downloadable'] == "on");
        $data['partner_instructor'] = (!empty($data['partner_instructor']) and $data['partner_instructor'] == "on");
        $data['subscribe'] = (!empty($data['subscribe']) and $data['subscribe'] == "on");
        $data['forum'] = (!empty($data['forum']) and $data['forum'] == "on");
        $data['private'] = (!empty($data['private']) and $data['private'] == "on");
        $data['enable_waitlist'] = (!empty($data['enable_waitlist']) and $data['enable_waitlist'] == "on");
        $data['only_for_students'] = (!empty($data['only_for_students']) and $data['only_for_students'] == "on");

        if (empty($data['partner_instructor'])) {
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

        $this->handleProductBadges($webinar, $data);

        unset($data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['product_badges'],
        );

        $newCreatorId = !empty($data['organ_id']) ? $data['organ_id'] : $data['teacher_id'];
        $changedCreator = ($webinar->creator_id != $newCreatorId);

        $data = $this->handleVideoDemoData($request, $newCreatorId, $data, "course_demo_" . time());

        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

        $webinar->update([
            'slug' => $data['slug'],
            'creator_id' => $newCreatorId,
            'teacher_id' => $data['teacher_id'],
            'type' => $data['type'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'icon' => $data['icon'] ?? null,
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'capacity' => $data['capacity'] ?? null,
            'sales_count_number' => $data['sales_count_number'] ?? null,
            'start_date' => $data['start_date'],
            'timezone' => $data['timezone'] ?? null,
            'duration' => $data['duration'] ?? null,
            'support' => $data['support'],
            'certificate' => $data['certificate'],
            'private' => $data['private'],
            'only_for_students' => $data['only_for_students'],
            'enable_waitlist' => $data['enable_waitlist'],
            'downloadable' => $data['downloadable'],
            'partner_instructor' => $data['partner_instructor'],
            'subscribe' => $data['subscribe'],
            'forum' => $data['forum'],
            'access_days' => $data['access_days'] ?? null,
            'price' => $data['price'],
            'organization_price' => $data['organization_price'] ?? null,
            'category_id' => $data['category_id'],
            'points' => $data['points'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'],
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

        if ($publish) {
            sendNotification('course_approve', ['[c.title]' => $webinar->title], $webinar->teacher_id);

            $createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
            RewardAccounting::makeRewardAccounting(
                $webinar->creator_id,
                $createClassesReward,
                Reward::CREATE_CLASSES,
                $webinar->id,
                true
            );

        } elseif ($reject) {
            sendNotification('course_reject', ['[c.title]' => $webinar->title], $webinar->teacher_id);
        }

        if ($changedCreator) {
            $this->webinarChangedCreator($webinar);
        }

        removeContentLocale();
        return true;
    }
}
