<?php

namespace App\Services\App;

use App\Http\Controllers\Panel\Traits\VideoDemoTrait;
use App\Models\Bundle;
use App\Models\BundleFilterOption;
use App\Models\Tag;
use App\Models\Translation\BundleTranslation;

class BundleCreateService
{
    use VideoDemoTrait;

    public function createBundle($request, $data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = Bundle::makeSlug($data['title']);
        }

        $data = $this->handleVideoDemoData($request, $data['teacher_id'], $data, "bundle_demo_" . time());

        $bundle = Bundle::create([
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
            'private' => (!empty($data['private']) and $data['private'] == 'on'),
            'subscribe' => (!empty($data['subscribe']) and $data['subscribe'] == "on"),
            'certificate' => (!empty($data['certificate']) and $data['certificate'] == "on"),
            'only_for_students' => (!empty($data['only_for_students']) and $data['only_for_students'] == "on"),
            'points' => $data['points'] ?? null,
            'price' => $data['price'],
            'access_days' => $data['access_days'] ?? null,
            'category_id' => $data['category_id'],
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => Bundle::$pending,
            'created_at' => time(),
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

        return getAdminPanelUrl() . '/bundles/' . $bundle->id . '/edit?locale=' . $data['locale'];
    }
}
