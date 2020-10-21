<?php

namespace Modules\TaskManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $project_id
 * @property string $name
 * @property boolean $is_archive
 * @property string $created_at
 * @property string $updated_at
 * @property Task[] $tasks
 */
class TaskList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tm_lists';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['project_id', 'name', 'is_archive', 'created_at', 'updated_at'];

    /**
     * @return HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
