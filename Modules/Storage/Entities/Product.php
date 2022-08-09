<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Country;

class Product extends Model
{


    use SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_DEMAND = 2;
    const TOTAL_DELETED = 3;
    protected $fillable = [
        'initial_amount',
//        'unit_id',
//        'less_value',
        'quickly_old',
        'title_id',
        'kind_id',
        'state_id',
        'description',
        'amount',
        'storage_id',
        'model_id',
//        'product_model',
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
        "company_id",
        'status',
        'sell_act_id',
        'room',
        'floor'
    ];

    public const CAT_UPDATE = [
        'product_mark',
        'color_id',
        'state_id',
        'less_value',
        'quickly_old',
        'main_funds',
        'description'
    ];
    protected $appends=[
      'count'
    ];

    public function getCountAttribute()
    {
        return $this->assignments()->select('amount')->where('assignment_type',ProductAssignment::ATTACHMENT_TYPE)->get();
    }

    public function deletes_logs(){
        return $this->hasMany(ProductDelete::class , 'product_id' , 'id');
    }
    public function updates_logs(){
        return $this->hasMany(ProductUpdate::class , 'product_id' , 'id');
    }

    protected $hidden = ['mark_id' , 'product_model'];

    public function kind(){
        return $this->belongsTo(ProductKind::class)->distinct();
    }
    public function unit(){
        return $this->belongsTo(Unit::class);
    }
    public function title(){
        return $this->belongsTo(ProductTitle::class);
    }
    public function model(){
        return $this->belongsTo(ProductModel::class);
    }
    public function state(){
        return $this->belongsTo(ProductState::class);
    }
    public function color(){
        return $this->belongsTo(ProductColor::class);
    }
    public function storage(){
        return $this->belongsTo(Storage::class);
    }
    public function assignments(){
        return $this->hasMany(ProductAssignment::class , 'product_id' , 'id');
    }
    public function deletes(){
        return $this->hasMany(ProductDelete::class , 'product_id' , 'id');
    }
    public function buy_from_country(){
        return $this->belongsTo(Country::class , 'buy_from_country');
    }
    public function made_in_country(){
        return $this->belongsTo(Country::class , 'made_in_country');
    }

    public function scopeCompany($q){
        return $q->where('company_id' , request('company_id'));
    }
}
