<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $module_id
 * @property integer $company_id
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property Module $module
 */
class CompanyModule extends Model
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
    protected $fillable = ['module_id', 'company_id', 'is_active', 'created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo('App\Models\Module');
    }
}
