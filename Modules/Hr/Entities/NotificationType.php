<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    protected $fillable = [
        'name', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];
}
