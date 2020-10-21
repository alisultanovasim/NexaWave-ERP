<?php

namespace Modules\TaskManager\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $user_id
 * @property integer $project_id
 * @property Project $project
 * @property User $user
 */
class ProjectUser extends Model
{

    protected $table="tm_project_users";

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'project_id'];

    /**
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
