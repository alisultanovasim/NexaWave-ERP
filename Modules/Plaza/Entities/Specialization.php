<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
