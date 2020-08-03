<?php

namespace Modules\Hr\Entities\EmployeeOrders;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderEmployee extends Model
{
    use SoftDeletes, Filterable;

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'json',
        'vacation_details' => 'json'
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

}
