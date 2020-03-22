<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GlobalLog extends Model
{

    protected  $table = 'global_logs';

    protected $guarded = ['id'];
    public  $timestamps  = false;

    protected $appends=['meta'];



    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public  function getMetaAttribute(){
        if (preg_match('/[0-9]+$/' , $this->getOriginal('description') , $f))
            return +$f[0];
        return null;
    }

    public function getDescriptionAttribute($value){
        if (preg_match('/[0-9]+$/' , $value , $f))
            return __(rtrim($value, "/{$f[0]}"));
        return __($value);
    }


}
