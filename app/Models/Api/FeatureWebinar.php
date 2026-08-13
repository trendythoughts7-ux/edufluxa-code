<?php

namespace App\Models\Api;

use App\Models\FeatureWebinar as Model;

class FeatureWebinar extends Model
{
    public function webinar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\Webinar', 'webinar_id', 'id');
    }
    public function scopeHandleFilters($query)
    {
        $request=request() ;
        $offset = $request->get('offset', null);
        $limit = $request->get('limit', null);
        $category = $request->get('cat', null);

        $query->whereHas('webinar', function ($q) {
            $q->where('status', \App\Models\Webinar::$active);
        });

        if (!empty($category) and is_numeric($category)) {

            // $query->with('webinar')  ;//->where('webinar.category_id', $category);
            $query->whereHas('webinar', function ($q) use ($category) {
                $q->where('category_id', $category);
            });
        }

        if (!empty($offset) && !empty($limit)) {
            $query->skip($offset);
        }
        if (!empty($limit)) {
            $query->take($limit);
        }
        return $query;
    }
}
