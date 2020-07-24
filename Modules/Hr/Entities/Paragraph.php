<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;

class Paragraph extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public function fields(){
        return $this->hasMany(ParagraphField::class);
    }
}
