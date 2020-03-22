<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class Region extends Model
{

    protected $table='esd_regions';

    public $timestamps = false;

    protected  $guarded = ['id'];
}
