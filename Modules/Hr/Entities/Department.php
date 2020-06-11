<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

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

    public function structuredSections(){
        return $this->hasMany(Section::class, 'structable_id', 'id')
            ->where('structable_type', 'department')
            ->with([
                'structuredSectors:id,name,structable_id,structable_type',
            ]);
    }

    public function positions(){
        return $this->belongsToMany(
            Positions::class,
            'structure_positions',
            'structure_id',
            'position_id'
        )
        ->withPivot('quantity')
        ->where('structure_type', 'department');
    }

    public function structuredSectors(){
        return $this->hasMany(Sector::class, 'structable_id', 'id')
            ->where('structable_type', 'department');
    }

    public function getStructureTypeAttribute(){
        return 'department';
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
