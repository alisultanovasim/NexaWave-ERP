<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Contract  extends Model
{

    protected $guarded = [];


    protected $table='office_contracts';

    public function getContractAttribute($value){
        if ($value)
            return env('APP_URL') . '/storage/' . $value;
        return $value;
    }
}
