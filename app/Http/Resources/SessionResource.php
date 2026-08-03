<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read bool|null $read
 * @property-read int $date
 * @property-read int $duration
 * @property-read string $description
 * @property-read int|string $created_at
 * @property-read string|null $moderator_secret
 * @property-read string|null $link
 * @property-read string|null $session_api
 * @property-read string|null $zoom_start_link
 * @property-read string|null $api_secret
 * @property-read bool|int|null $check_previous_parts
 * @property-read int|null $access_after_day
 * @property-read string|null $agora_settings
 */
class SessionResource extends JsonResource
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
            'content_type' => 'session',
            'title' => $this->title,
            'can_view_error' => $this->canViewError(),
            'auth_has_read' => $this->read,
            'date' => $this->date,
            'duration' => $this->duration,
            'description' => $this->description,
            'created_at' => $this->created_at,

            //   'user_has_access' => $this->user_has_access,
            'is_finished' => $this->isFinished(),
            'is_started'=>(time() > $this->date) ,
            // 'status' => $this->status,
            // 'order' => $this->order,
            'moderator_secret' => $this->moderator_secret,

            'link' => $this->link,
            'join_link' => (apiAuth()) ? $this->getJoinLink() : null,
            'can_join'=>(apiAuth() and !$this->isFinished() and time() > $this->date ) ,
            'session_api' => $this->session_api,
            'zoom_start_link' => $this->zoom_start_link,
            // 'session_api' => $this->session_api,
            'api_secret' => $this->api_secret,
            'check_previous_parts' => $this->check_previous_parts,
            'access_after_day' => $this->access_after_day,

            // 'updated_at' => $this->updated_at,
            'agora_settings ' => $this->agora_settings,

        ];

    }
}
