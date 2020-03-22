<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected  $guarded  = ['id'];
}
