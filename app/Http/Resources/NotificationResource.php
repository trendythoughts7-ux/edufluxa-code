<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $sender_id
 * @property int|null $group_id
 * @property int|null $webinar_id
 * @property string $title
 * @property string $message
 * @property string $sender
 * @property string $type
 * @property int $created_at
 */
class NotificationResource extends JsonResource
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
            "id" => $this->id,
            "user_id" => $this->user_id,
            "sender_id" => $this->sender_id,
            "group_id" => $this->group_id,
            "webinar_id" => $this->webinar_id,
            "title" => $this->title,
            "message" => $this->message,
            "sender" => $this->sender,
            "type" => $this->type,
            "created_at" => $this->created_at,
        ];
    }
}
