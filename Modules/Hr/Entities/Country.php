<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property string $code
 * @property string $iso
 * @property string $iso3
 * @property string $phone_code
 * @property string $currency
 * @property integer $index
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property City[] $cities
 * @property Organization[] $organizations
 */
class Country extends Model
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
    protected $fillable = ['name', 'short_name', 'code', 'iso', 'iso3', 'phone_code', 'currency', 'index', 'is_active', 'created_at', 'updated_at' ];

    /**
     * @return HasMany
     */
    public function cities()
    {
        return $this->hasMany('Modules\Hr\EntitiesCity');
    }

    /**
     * @return HasMany
     */
    public function organizations()
    {
        return $this->hasMany('Modules\Hr\EntitiesOrganization');
    }
}
