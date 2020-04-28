<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $table = 'currency';
    protected $fillable = ['name' , 'code' , 'char'];

}
