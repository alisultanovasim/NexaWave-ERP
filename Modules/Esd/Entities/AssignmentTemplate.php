<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class AssignmentTemplate extends  Model
{
    protected  $table = 'assignment_templates';
    protected  $guarded = ['id'];
    public $timestamps = false;
}
