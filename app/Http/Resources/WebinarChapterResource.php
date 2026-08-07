<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Api\WebinarChapter
 * @property-read int $id
 * @property-read string $title
 * @property-read int|string $created_at
 * @property-read bool|int $check_all_contents_pass
 * @property-read \Illuminate\Database\Eloquent\Collection $chapterItems
 */
class WebinarChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'topics_count' => $this->getTopicsCount(),
            'created_at' => $this->created_at,
            'check_all_contents_pass' => $this->check_all_contents_pass,
            'items' => WebinarChapterItemsResource::collection($this->chapterItems)
            //'duration' => convertMinutesToHourAndMinute($this->getDuration()),
            // 'status' => $this->status,
            //  'order' => $this->order,
        ];
    }
}
