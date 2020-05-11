<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'office_documents';

    public  $timestamps = false;

    public function getUrlAttribute($value){
        if ($value)
            return env('APP_URL') . '/documents/'  .  $value;
        return $value;
    }

}
