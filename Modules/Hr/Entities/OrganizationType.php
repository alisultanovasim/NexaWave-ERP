<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property string $name
 * @property string $suffix
 * @property integer $index
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class OrganizationType extends Model
{
    use SoftDeletes;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'suffix', 'index', 'company_id', 'created_at', 'updated_at', 'deleted_at'];


}
