<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Demand extends Model
{
    const STATUS_WAIT=1;
    const STATUS_CONFIRMED=2;
    const STATUS_REJECTED=3;
    const DRAFT=1;
    const NOT_DRAFT=2;
    const DIRECTOR_ROLE=8;
    const SAILOR_ROLE=43;
    const FINANCIER_ROLE=25;


    use SoftDeletes;
protected $fillable=[
    'name',
    'description',
    'title',
    'title_id',
    'kind',
    'kind_id',
    'model',
    'mark',
    'model_id',
    'type_of_doc',
    'attachment',
    'amount',
    'employee_id',
    'company_id',
    'progress_status',
    'status',
];

public function propose()
{
   return $this->hasOne(Propose::class);
}
public function employee(){
    return $this->belongsTo(Employee::class);
}
public function assignment(){
    return $this->hasOne(DemandAssignment::class);
}


public function scopeCompany($q){
    return $q->where('company_id' , request(
        'company_id'
    ));
}

    public function title()
    {
        return $this->belongsTo(ProductTitle::class);
}

    public function kind()
    {
        return $this->belongsTo(ProductKind::class);
}

    public function model()
    {
        return $this->belongsTo(ProductModel::class);
}

    public function corrects()
    {
        return $this->hasMany(DemandCorrect::class);
}

}
