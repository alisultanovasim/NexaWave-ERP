<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $guarded = ['id'];

    public  $timestamps = false;

    protected $table = "office_workers";

    public function office(){
        return $this->belongsTo('Modules\Plaza\Entities\Office');
    }
    public function card(){
        return $this->belongsTo('Modules\Plaza\Entities\Card' , 'card');
    }
    public function role(){
        return $this->belongsTo('Modules\Plaza\Entities\Role' );
    }

}
