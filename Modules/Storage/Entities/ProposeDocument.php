<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class ProposeDocument extends Model
{

    use SoftDeletes;
    protected $fillable=[
      'status',
      'demand_id',
      'company_id',
      'offer_file',
      'description',
      'send_back',
      'progress_status'
    ];
    const STATUS_WAIT=1;
    const STATUS_REJECTED=2;
    const STATUS_CONFIRMED=3;

    const DIRECTOR_ROLE=8;
    const FINANCIER_ROLE=25;
    const PURCHASED_ROLE=42;

    public function selectedProposeDetails()
    {
        return $this->hasMany(ProposeDetail::class)->where('selected',1);
    }

    public function proposeDetails()
    {
        return $this->hasMany(ProposeDetail::class);
    }

    public function proposes()
    {
        return $this->hasMany(Propose::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function archive()
    {
        return $this->belongsTo(ArchiveDocument::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class,'id','employee_id')->with(['user:id,name']);
    }
}
