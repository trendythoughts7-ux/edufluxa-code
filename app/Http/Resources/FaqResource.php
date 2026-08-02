<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string $answer
 * @property int|null $order
 * @property int $created_at
 * @property int $updated_at
 */
class FaqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       return [
           'id'=>$this->id ,
           'title'=>$this->title ,
           'answer'=>$this->answer ,
           'order'=>$this->order ,
           'created_at'=>$this->created_at ,
           'updated_at'=>$this->updated_at
       ] ;
    }
}
