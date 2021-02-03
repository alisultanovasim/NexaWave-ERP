<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Traits\HasCuratorRelation;

class Department extends Model
{
    use SoftDeletes, HasCuratorRelation;

    protected $guarded = [];

    protected $hidden = [
        'structable_type',
        'structable_id'
    ];

    protected $appends = ['structure_type'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function children(): HasMany
    {
        return $this->sections();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function structuredSections(): HasMany
    {
        return $this->hasMany(Section::class, 'structable_id', 'id')
            ->where('structable_type', 'department')
            ->with([
                'curator',
                'structuredSectors:id,name,structable_id,structable_type',
            ]);
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
        ->where('structure_type', 'department');
    }

    public function structuredSectors(): HasMany
    {
        return $this->hasMany(Sector::class, 'structable_id', 'id')
            ->with([
                'curator',
            ])
            ->where('structable_type', 'department');
    }

    public function getStructureTypeAttribute(): string
    {
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
        ]);
    }
}
