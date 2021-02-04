<?php

namespace Modules\TaskManager\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property integer $id
 * @property integer $task_id
 * @property integer $user_id
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 * @property Task $task
 * @property User $user
 */
class TaskComment extends Model
{
    protected $table = "tm_task_comments";

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid();
        });
    }

    protected $primaryKey = "id";
    /**
     * @var array
     */
    protected $fillable = ['task_id', 'user_id', 'comment', 'created_at'];

    protected $hidden = [
        'updated_at'
    ];

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
