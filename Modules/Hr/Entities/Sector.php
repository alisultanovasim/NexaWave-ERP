<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function positions(){
        return $this->belongsToMany(
            Positions::class,
            'structure_positions',
            'structure_id',
            'position_id'
        )
        ->withPivot('quantity')
        ->where('structure_type', 'sector');
    }

    public function getStructureTypeAttribute(){
        return 'sector';
    }
}
