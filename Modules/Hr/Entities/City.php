<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property integer $id
 * @property integer $country_id
 * @property string $name
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property Country $country
 * @property Region[] $regions
 */
class City extends Model
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
    protected $fillable = ['country_id', 'name', 'is_active', 'created_at', 'updated_at', 'phone_code', 'position'];

    /**
     * @return BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('Modules\Hr\Entities\Country');
    }

    /**
     * @return HasMany
     */
    public function regions()
    {
        return $this->hasMany('Modules\Hr\Entities\Region');
    }
}
