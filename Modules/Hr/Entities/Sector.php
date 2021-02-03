<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Traits\HasCuratorRelation;

class Sector extends Model
{
    use SoftDeletes, HasCuratorRelation;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(
            Positions::class,
            'structure_positions',
            'structure_id',
            'position_id'
        )
        ->withPivot('quantity')
        ->where('structure_type', 'sector');
    }

    public function getStructureTypeAttribute(): string
    {
        return 'sector';
    }
}
