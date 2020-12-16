<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    const FILE = 1;
    const EDITOR = 2;
    protected $guarded = ["id"];

    protected $table = "docs";

    public $timestamps = false;

    public function subDocs()
    {
        return $this->hasMany("Modules\Esd\Entities\Doc", "parent_id");
    }

    public function uploader()
    {
        return $this->belongsTo("Modules\Esd\Entities\User", "uploader", "id")->select(["id", "name"]);
    }

    public function getResourceAttribute($value)
    {
        if ($this->type == self::FILE and $value)
            return config('app.url') . '/storage/' . $value;
        return $value;
    }


}
