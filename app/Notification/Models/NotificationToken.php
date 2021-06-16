<?php

namespace App\Notification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property integer $user_id
 * @property string $fcm_token
 * @property integer $application_id
 * @property string $user_ip
 * @property string $user_agent
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class NotificationToken extends Model
{

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */


    protected $keyType = 'string';
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'fcm_token', 'application_id', 'user_ip', 'user_agent', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->{$log->getKeyName()} = (string)Str::uuid();
        });
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
