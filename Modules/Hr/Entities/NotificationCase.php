<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationCase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type_id', 'notify_period', 'notify_admin', 'notify_user', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function type(){
        return $this->belongsTo('Modules\Hr\EntitiesNotificationType', 'type_id', 'id');
    }
}
