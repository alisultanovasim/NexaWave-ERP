<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    const TYPE_RAW = 1;
    const TYPE_ACTION = 1;
    protected $fillable = ['type' , 'position' ,'name' , 'is_active' , 'field'];

}
