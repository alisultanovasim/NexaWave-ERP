<?php

namespace Modules\TaskManager\Entities;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * @property integer $id
 * @property integer $company_id
 * @property integer $contract_id
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Company $company
 * @property User[] $users
 * @method Builder isActive()
 */
class Project extends Model
{

    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = "tm_projects";
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = [
        'company_id',
        'contract_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'tm_project_users');
    }


    /**
     * @param $q
     * @return mixed
     */
    public function scopeIsActive($q)
    {
        return $q->where("is_active", "=", true);
    }
}
