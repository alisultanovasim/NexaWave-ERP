<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductAssignment extends Model
{
    protected $guarded = [];


    const ASSIGN_TO_USER = 1 ;
    const ASSIGN_TO_PLACE = 2 ;

    public function scopeCompany($q){
        return $q->where('company_id', request('company_id'));
    }

    public function employee(){}
    public function department(){}
    public function section(){}
    public function sector(){}

}
