<?php

namespace Modules\TaskManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

/**
 * @property integer $id
 * @property integer $project_id
 * @property string $name
 * @property boolean $is_archive
 * @property string $created_at
 * @property string $updated_at
 * @property Task[] $tasks
 * @property Project $project
 * @method Builder isNotArchive()
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
    protected $fillable = ['project_id', 'name', 'is_archive'];

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    /**
     * @return HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, "project_id");
    }

    /**
     * @param $q
     * @return mixed
     */
    public function scopeIsNotArchive($q)
    {
        return $q->where("is_archive", false);
    }
}
