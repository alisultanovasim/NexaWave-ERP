<?php

namespace Modules\Hr\Entities\EmployeeOrders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderEmployee extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
}
