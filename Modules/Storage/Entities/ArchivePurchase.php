<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class ArchivePurchase extends Model
{
    use SoftDeletes;

    protected $table='archive_rejected_purchases';
    protected $fillable=['from_id','purchase_id','reason','is_active'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'from_id');
    }
}
