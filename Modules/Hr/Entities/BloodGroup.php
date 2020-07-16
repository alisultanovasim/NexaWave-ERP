<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property integer $id
 * @property string $name
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property EmployerPassportInformation[] $employerPassportInformations
 */
class BloodGroup extends Model
{
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
    protected $fillable = ['name', 'is_active', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employerPassportInformations()
    {
        return $this->hasMany('Modules\Hr\EntitiesEmployerPassportInformation');
    }
}
