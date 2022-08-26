<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class   ProductModel extends Model
{
    protected $table='product_models';
    protected $fillable=['kind_id','name'];

    public function kind()
    {
        return $this->belongsTo(ProductKind::class);
    }
//    protected $guarded = [];
}
