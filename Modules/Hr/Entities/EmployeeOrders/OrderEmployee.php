<?php

namespace Modules\Hr\Entities\EmployeeOrders;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class OrderEmployee extends Model
{
    use SoftDeletes, Filterable, QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'json'
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

}
