<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class NotificationCase extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $fillable = [
        'type_id', 'notify_period', 'notify_admin', 'notify_user', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function type(){
        return $this->belongsTo(NotificationType::class, 'type_id', 'id');
    }
}
