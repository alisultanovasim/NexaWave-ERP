<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Contract  extends Model
{

    protected $guarded = [];


    protected $table='office_contracts';

    public function getContractAttribute($value){
        if ($value)
            return config('app.url') . '/storage/' . $value;
        return $value;
    }
}
