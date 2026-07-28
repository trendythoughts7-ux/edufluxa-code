<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSelectedSpecificationMultiValue extends Model
{
    protected $table = 'product_selected_specification_multi_values';
    public $timestamps = false;
    protected $guarded = ['id'];


    public function selectedSpecification(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ProductSelectedSpecification','selected_specification_id','id');
    }

    public function multiValue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\ProductSpecificationMultiValue','specification_multi_value_id','id');
    }
}
