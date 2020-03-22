<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $city_id
 * @property string $name
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property City $city
 */
class Region extends Model
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
    protected $fillable = ['city_id', 'name', 'is_active', 'created_at', 'updated_at' ,'company_id'];

    /**
     * @return BelongsTo
     */
    public function city()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCity');
    }
}
