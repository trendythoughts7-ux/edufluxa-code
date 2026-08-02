<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string|null $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductCategory[] $subCategories
 */
class ProductCategoryResource extends JsonResource
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
            'icon' => ($this->icon) ? url($this->icon) : null,
            'subCategories' => $this->subCategories
        ];
    }
}
