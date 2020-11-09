<?php

namespace Modules\TaskManager\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $list_id
 * @property integer $parent_id
 * @property integer $assigned_id
 * @property integer $created_id
 * @property integer $status
 * @property string $name
 * @property string $deadline
 * @property string $description
 * @property float $budget
 * @property string $created_at
 * @property string $updated_at
 * @property User $assigned
 * @property User $createdBy
 * @property TaskList $list
 * @property Task $parent
 * @property Task[] $subTasks
 * @property TaskComment[] $comments
 * @property TaskFile[] $files
 * @property User[] $watchers
 * @property ActivityLog[] $activity
 */
class Task extends Model
{

    /**
     * Task statutes
     */
    const PENDING = 1;

    const IN_PROGRESS = 2;

    const COMPLETED = 3;

    public static function statuses()
    {
        return [self::PENDING, self::IN_PROGRESS, self::COMPLETED];
    }

    /**
     * @var string
     */
    protected $table = "tm_tasks";

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
        'list_id',
        'parent_id',
        'assigned_id',
        'created_id',
        'name',
        'deadline',
        'description',
        'budget',
        'status',
        'created_at',
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'updated_at'

    ];

    /**
     * @return BelongsTo
     */
    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    /**
     * @return BelongsTo
     */
    public function list()
    {
        return $this->belongsTo(TaskList::class);
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function subTasks()
    {
        return $this->hasMany(Task::class, "id", "parent_id");
    }

    /**
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * @return HasMany
     */
    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

    /**
     * @return BelongsToMany
     */
    public function watchers()
    {
        return $this->belongsToMany(User::class, 'tm_task_watchers');
    }

    /**
     * @return HasMany
     */
    public function activity()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
