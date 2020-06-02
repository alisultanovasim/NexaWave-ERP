<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected  $table = 'document_sections';
    CONST RULES = [
        1 => 'structure_docs',
        2 => 'citizen_docs',
        3 => 'structure_docs',
        4 => 'citizen_docs' ,
        5 => 'in_company_docs'
    ];


    public function documents(){
        return $this->hasMany(Document::class , 'section_id' , 'id');
    }


    protected $guarded = ["id"];

    public $timestamps = false;
}
