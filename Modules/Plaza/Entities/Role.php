<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected  $table = 'office_roles';
    public $timestamps = false;

    protected  $guarded  = ['id'];
}
