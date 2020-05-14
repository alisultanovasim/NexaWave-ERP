<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'unit_id',
        'less_value',
        'quickly_old',
        'title_id',
        'kind_id',
        'state_id',
        'description',
        'amount',
        'storage_id',
        'product_model',
        'product_mark',
        'product_no',
        'color_id',
        'main_funds',
        'inv_no',
        'exploitation_date',
        'size',
        'made_in_country',
        'buy_from_country',
        'make_date',
        "company_id"
    ];

    protected $hidden = ['mark_id'];

    public function kind(){
        return $this->belongsTo(ProductKind::class);
    }
    public function title(){
        return $this->belongsTo(ProductTitle::class);
    }

    public function state(){
        return $this->belongsTo(ProductState::class);
    }

    public function color(){
        return $this->belongsTo(ProductColor::class);
    }
}
