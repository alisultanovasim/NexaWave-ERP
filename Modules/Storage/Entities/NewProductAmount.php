<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class NewProductAmount extends Model
{
    use SoftDeletes;
    protected $table='new_product_amounts';
    protected $fillable=[
        'amount',
        'process_date',
        'employee_id',
        'company_id',
        'product_id',
        'price',
        'total_price'
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
