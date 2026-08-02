<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property int|null $pass_mark
 * @property float|null $average_grade
 * @property int $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Certificate[] $certificates
 */
class CertificateResource extends JsonResource
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
          //  'title' => $this->id,
            'quiz_title' => $this->title,
            'webinar_title' => !empty($this->webinar) ? $this->webinar->title : '',
            'pass_mark' => $this->pass_mark,
            'average_grade' => $this->average_grade,
            'certificates_count' => $this->certificates->count(),
            'created_at' => $this->created_at,
        ];
    }
}


