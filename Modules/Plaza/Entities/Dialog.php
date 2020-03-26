<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Dialog extends Model
{
    public  $timestamps = false;
    protected $guarded = ['id'];
    public function messages(){
        return $this->hasMany('Modules\Plaza\Entities\Message');
    }

    public function kind(){
        return $this->belongsTo('Modules\Plaza\Entities\Kind');
    }
    public function office(){
        return $this->belongsTo('Modules\Plaza\Entities\Office');
    }

    public function user(){
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee' , 'assigned_user' , "id");
    }
}
