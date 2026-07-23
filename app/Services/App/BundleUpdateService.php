<?php

namespace App\Services\App;

use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Tag;
use App\Models\Translation\BundleTranslation;
use App\User;

class BundleUpdateService
{
    use VideoDemoTrait;
    use ProductBadgeTrait;

    public function checkBundleTeacherOwnership($data, $bundle)
    {
        if (!empty($data['teacher_id'])) {
            $teacher = User::findOrFail($data['teacher_id']);
            $creator = $bundle->creator;

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

    public function updateBundle($request, $bundle, $data)
    {
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
        $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

        if (empty($data['slug'])) {
            $data['slug'] = Bundle::makeSlug($data['title']);
        }

        $data['status'] = $publish ? Bundle::$active : ($reject ? Bundle::$inactive : ($isDraft ? Bundle::$isDraft : Bundle::$pending));
        $data['updated_at'] = time();

        $data['private'] = (!empty($data['private']) and $data['private'] == 'on');
        $data['subscribe'] = (!empty($data['subscribe']) and $data['subscribe'] == "on");
        $data['certificate'] = (!empty($data['certificate']) and $data['certificate'] == "on");
        $data['only_for_students'] = (!empty($data['only_for_students']) and $data['only_for_students'] == "on");

        if ($data['category_id'] != $bundle->category_id) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            BundleFilterOption::where('bundle_id', $bundle->id)->delete();

            foreach ($filters as $filter) {
                BundleFilterOption::create([
                    'bundle_id' => $bundle->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('bundle_id', $bundle->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'bundle_id' => $bundle->id,
                    'title' => $tag,
                ]);
            }
        }

        // Product Badge
        $this->handleProductBadges($bundle, $data);

        unset($data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['product_badges']
        );

        $data = $this->handleVideoDemoData($request, $data['teacher_id'], $data, "bundle_demo_" . time());

        $bundle->update([
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'private' => $data['private'],
            'subscribe' => $data['subscribe'],
            'certificate' => $data['certificate'],
            'only_for_students' => $data['only_for_students'],
            'points' => $data['points'] ?? null,
            'price' => $data['price'],
            'access_days' => $data['access_days'] ?? null,
            'category_id' => $data['category_id'],
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'],
            'updated_at' => time(),
        ]);

        if ($bundle) {
            BundleTranslation::updateOrCreate([
                'bundle_id' => $bundle->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'summary' => $data['summary'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        $notifyOptions = [
            '[item_title]' => $bundle->title,
        ];

        if ($publish) {
            sendNotification('bundle_approved', $notifyOptions, $bundle->teacher_id);

            /*$createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
            RewardAccounting::makeRewardAccounting(
                $bundle->creator_id,
                $createClassesReward,
                Reward::CREATE_CLASSES,
                $bundle->id,
                true
            );*/

        } elseif ($reject) {
            sendNotification('bundle_rejected', $notifyOptions, $bundle->teacher_id);
        }

        removeContentLocale();

        return true;
    }
}
