<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property integer $id
 * @property integer $organization_link_id
 * @property integer $profession_id
 * @property integer $education_level_id
 * @property float $profession_salary
 * @property int $vacancy_count
 * @property int $generation
 * @property integer $index
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property EducationLevel $educationLevel
 * @property OrganizationLink $organizationLink
 * @property Profession $profession
 */
class ProfessionLink extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['organization_link_id', 'profession_id', 'education_level_id',
        'profession_salary', 'vacancy_count', 'generation', 'index', 'company_id',
        'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return BelongsTo
     */
    public function educationLevel()
    {
        return $this->belongsTo('Modules\Hr\EntitiesEducationLevel');
    }

    /**
     * @return BelongsTo
     */
    public function organizationLink()
    {
        return $this->belongsTo('Modules\Hr\EntitiesOrganizationLink');
    }

    /**
     * @return BelongsTo
     */
    public function profession()
    {
        return $this->belongsTo('Modules\Hr\EntitiesProfession');
    }
}
