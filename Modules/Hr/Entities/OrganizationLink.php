<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $organization_id
 * @property integer $department_id
 * @property integer $sector_id
 * @property integer $section_id
 * @property integer $position
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Department $department
 * @property Organization $organization
 * @property Section $section
 * @property Sector $sector
 * @property ProfessionLink[] $professionLinks
 */
class OrganizationLink extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['organization_id', 'department_id', 'sector_id', 'section_id', 'position', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return BelongsTo
     */
    public function department()
    {
        return $this->belongsTo('Modules\Hr\EntitiesDepartment');
    }

    /**
     * @return BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo('Modules\Hr\EntitiesOrganization');
    }

    /**
     * @return BelongsTo
     */
    public function section()
    {
        return $this->belongsTo('Modules\Hr\EntitiesSection');
    }

    /**
     * @return BelongsTo
     */
    public function sector()
    {
        return $this->belongsTo('Modules\Hr\EntitiesSector');
    }

    /**
     * @return HasMany
     */
    public function professionLinks()
    {
        return $this->hasMany('Modules\Hr\EntitiesProfessionLink');
    }
}
