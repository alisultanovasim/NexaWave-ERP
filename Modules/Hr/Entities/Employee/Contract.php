<?php


namespace Modules\Hr\Entities\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class Contract extends Model
{

    protected $casts = [
        'rest_days' => 'json',
        'versions' => 'json',
        'additions' => 'json',
    ];
    const ACTIVE = 1;

    // w - week
    // m - month
    // t - term
    // y - year
    const AWARD_PERIODS = [
        'w' , 'm', 't' ,'y'
    ];


    const WORK_PLACE_TYPES = [
        'main', 'extra'
    ];

    const WEEK_DAYS = [
        'sun' , 'mon', 'tues' ,'wed', 'thus','fri','sat'
    ];

    protected  $guarded = [];

    protected $table = 'employee_contracts';


    public function employee(){
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee' , 'employee_id', 'id');
    }
    public function department(){
        return $this->belongsTo('Modules\Hr\Entities\Department' ,'department_id' , 'id' );
    }
    public function personalCategory(){
        return $this->belongsTo('Modules\Hr\Entities\PersonalCategory');
    }

    public function specializationDegree(){
        return $this->belongsTo('Modules\Hr\Entities\SpecializationDegree');

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
    public function currency(){
        return $this->belongsTo('Modules\Hr\Entities\Currency' );
    }
    public function contract_type(){
        return $this->belongsTo('Modules\Hr\Entities\ContractType' );
    }
    public function duration_type(){
        return $this->belongsTo('Modules\Hr\Entities\DurationType' );
    }
    public function scopeActive($q){
        return $q -> where('is_active' , true)
            ->where('draft' , 0)
            ->whereNull('initial_contract_id');
    }

    public function scopeDraft($q){
        return $q -> where('is_active' , true)
            ->where('draft' , 1)
            ->whereNull('initial_contract_id');
    }

    public function scopeCurrentlyActive($query){
        return $query->where('is_active', true)
                ->where('start_date', '<', Carbon::now())
                ->where('end_date', '>', Carbon::now())
                ->where('is_terminated', false);
    }

    public function scopeNoInitial($q){
        return $q->whereNull('initial_contract_id');
    }
}
