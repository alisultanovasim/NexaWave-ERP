<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class StorageProduct extends Model
{
    protected $fillable=[
      'name'
    ];
    protected $table='storages';
}
