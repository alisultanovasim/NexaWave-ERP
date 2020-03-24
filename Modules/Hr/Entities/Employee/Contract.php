<?php


namespace Modules\Hr\Entities\Employee;
use Illuminate\Database\Eloquent\Model;


class Contract extends Model
{

    protected  $guarded = [];

    protected $table = 'employee_contracts';


    public function employee(){
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee' , 'employee_id', 'id');
    }
    public function department(){
        return $this->belongsTo('Modules\Hr\Entities\Department' );
    }
    public function section(){
        return $this->belongsTo('Modules\Hr\Entities\Section' );
    }
    public function sector(){
        return $this->belongsTo('Modules\Hr\Entities\Sector' );
    }
    public function position(){
        return $this->belongsTo('Modules\Hr\Entities\Positions' );
    }
    public function scopeActive($q){
        return $q -> where('is_active' , true);
    }

}
