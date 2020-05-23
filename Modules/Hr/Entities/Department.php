<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function children(){
        return $this->sections();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeWithAllRelations($query){
        return $query->with([
            'country' => function ($query){
                $query->select(['id', 'name']);
            },
            'city' => function ($query){
                $query->select(['id', 'name']);
            },
            'region' => function ($query){
                $query->select(['id', 'name']);
            },
            'sections' => function ($query){
                $query->select(['id', 'name', 'department_id']);
            },
//            'organization' => function ($query){
//                $query->select(['id', 'name']);
//            },
        ]);
    }
}
