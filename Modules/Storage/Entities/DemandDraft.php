<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class DemandDraft extends Model
{
    use SoftDeletes;
    const STATUS_WAIT=1;
    const STATUS_CONFIRMED=2;
    const STATUS_REJECTED=3;
    const SUPPLIER_ROLE=43;

    protected $table='demand_drafts';
    protected $fillable=[
        'name',
        'description',
        'attachment',
        'employee_id',
        'company_id',
        'status',
        'return_status',
        'is_sent'
    ];

    public function items()
    {
        return $this->hasMany(DemandDraftItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function archive()
    {
        return $this->belongsTo(ArchiveDocument::class);
    }

    public function delete(){
        $this->items()->delete();
        return parent::delete();
    }

}
