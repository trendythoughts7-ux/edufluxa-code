<?php

namespace App\Services\App;

use App\Models\Webinar;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PanelWebinarSearchService
{
    public function search(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization()) {
            return response('', 422);
        }
        $term = $request->get('term', null);
        $webinarId = $request->get('webinar_id', null);
        $option = $request->get('option', null);
        if (!empty($term)) {
            $query = Webinar::query()->select('id', 'teacher_id')
                ->where('only_for_students', false)
                ->whereTranslationLike('title', '%' . $term . '%')
                ->where('id', '<>', $webinarId)
                ->with(['teacher' => function ($query) {
                    $query->select('id', 'full_name');
                }]);
            $webinars = $query->get();
            $result = [];
            foreach ($webinars as $webinar) {
                $result[] = [
                    'id' => $webinar->id,
                    'title' => $webinar->title . ' - ' . $webinar->teacher->full_name,
                ];
            }
            return response()->json($result, 200);
        }
        return response('', 422);
    }

    public function getTags(Request $request, $id)
    {
        $webinarId = $request->get('webinar_id', null);
        if (!empty($webinarId)) {
            $tags = Tag::select('id', 'title')
                ->where('webinar_id', $webinarId)
                ->get();
            return response()->json($tags, 200);
        }
        return response('', 422);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);
        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }
        $user = auth()->user();
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
        if (!empty($webinar)) {
            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];
            if (!empty($webinar->$relation)) {
                $item = $webinar->$relation->where('id', $itemId)->first();
                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }
                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }
        abort(403);
    }
}
