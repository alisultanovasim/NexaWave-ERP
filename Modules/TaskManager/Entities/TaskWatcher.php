<?php

namespace Modules\TaskManager\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $user_id
 * @property integer $task_id
 * @property Task $task
 * @property User $user
 */
class TaskWatcher extends Model
{
    protected $table = "tm_task_watchers";

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'task_id'];

    /**
     * @return BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
