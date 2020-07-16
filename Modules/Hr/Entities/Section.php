<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Section extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function structuredSectors(){
        return $this->hasMany(Sector::class, 'structable_id', 'id')
            ->where('structable_type', 'section');
    }

    public function positions(){
        return $this->belongsToMany(
            Positions::class,
            'structure_positions',
            'structure_id',
            'position_id'
        )
        ->withPivot('quantity')
        ->where('structure_type', 'section');
    }

    public function scopeWithAllRelations($query){
        return $query->with([
            'department' => function ($query){
                $query->select(['id', 'name']);
            },
            'sectors' => function ($query){
                $query->select(['id', 'name']);
            },
        ]);
    }

    public function getStructureTypeAttribute(){
        return 'section';
    }

    public function children(){
        return $this->sectors();
    }
}
