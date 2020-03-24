<?php

namespace Modules\Entities;

use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    protected $guarded = ["id"];

    protected $table = "docs";

    public $timestamps = false;

    public function subDocs(){
        return $this->hasMany("Modules\Entities\Doc" , "parent_id");
    }

    public  function  uploader(){
        return $this->belongsTo("Modules\Entities\User" , "uploader" , "id")->select(["id" , "name"]);
    }

    public function getResourceAttribute($value){
        if ($this->type == config('esd.document.type.file') and $value)
            return env('API_GATEWAY_STATIC_FILES') . 'Esd?path=' . 'documents/' .  $value;
        return $value;
    }
}
