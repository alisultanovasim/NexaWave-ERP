<?php


namespace Modules\Hr\Entities\Employee;
use Illuminate\Database\Eloquent\Model;


class Contract extends Model
{

    protected $casts = [
        'rest_days' => 'json',
        'versions' => 'json',
        'additions' => 'json',
    ];
    const ACTIVE = 1;
    const DRAFT = 2;

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
        return $this->belongsTo('Modules\Hr\Entities\Department' );
    }
    public function personalCategory(){
        return $this->belongsTo('Modules\Hr\Entities\PersonalCategory', '');
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
        return $q -> where('is_active' , true);
    }
}
