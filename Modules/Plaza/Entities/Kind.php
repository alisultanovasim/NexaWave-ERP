<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Kind extends Model
{
    public $timestamps = false;

    protected $fillable = ['title'];
}
