<?php

namespace Modules\TaskManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Esd\Entities\User;

/**
 * @property integer $user_id
 * @property integer $task_id
 * @property string $id
 * @property string $action
 * @property string $lang
 * @property string $user_ip
 * @property string $user_agent
 * @property string $created_at
 * @property string $updated_at
 * @property Task $task
 * @property User $user
 */
class ActivityLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tm_activity_log';
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'task_id',
        'id',
        'action',
        'lang',
        'user_ip',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($log) {
            $log->{$log->getKeyName()} = Str::uuid();
        });
    }

    /**
     * @return BelongsTo
     */
    public function task()
    {
        return $this->belongsTo('Modules\TaskManager\Entities\TmTask', 'task_id');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Modules\TaskManager\Entities\User');
    }
}
