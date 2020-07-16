<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Currency extends Model
{
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $table = 'currency';
    protected $fillable = ['name' , 'code' , 'char'];

}
