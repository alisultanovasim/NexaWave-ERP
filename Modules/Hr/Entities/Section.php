<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
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

    public function children(){
        return $this->sectors();
    }
}
