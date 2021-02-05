<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = ["id"];

    public function document()
    {
        return $this->belongsTo("Modules\Esd\Entities\Document");
    }

    public function items()
    {
        return $this->hasMany('Modules\Esd\Entities\AssignmentItem', 'assignment_id');
    }

    public function item()
    {
        return $this->hasOne('Modules\Esd\Entities\AssignmentItem', 'assignment_id');
    }
    public function stuck()
    {
        return $this->hasOne('Modules\Esd\Entities\AssignmentItem', 'assignment_id');
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploader_user_id');
    }

    public function setVersionsAttribute($value)
    {

        if ($value == []) $inserted = "[]";
        else $inserted = json_encode($value);
        $this->attributes["versions"] = $inserted;
    }

    public function getVersionsAttribute($value)
    {
        return json_decode($value, true);
    }

}
