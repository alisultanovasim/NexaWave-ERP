<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationState extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'position' , 'company_id'];
}
