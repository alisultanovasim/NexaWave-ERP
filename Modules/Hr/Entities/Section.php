<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Traits\HasCuratorRelation;

class Section extends Model
{
    use SoftDeletes, HasCuratorRelation;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sectors(): HasMany
    {
        return $this->hasMany(Sector::class);
    }

    public function structuredSectors(): HasMany
    {
        return $this->hasMany(Sector::class, 'structable_id', 'id')
            ->with([
                'curator',
            ])
            ->where('structable_type', 'section');
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
        ->where('structure_type', 'section');
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'department' => function ($query){
                $query->select(['id', 'name']);
            },
            'sectors' => function ($query){
                $query->select(['id', 'name']);
            },
        ]);
    }

    public function getStructureTypeAttribute(): string
    {
        return 'section';
    }

    public function children(): HasMany
    {
        return $this->sectors();
    }
}
