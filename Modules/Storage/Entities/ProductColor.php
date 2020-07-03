<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{

    protected $table = 'colors';
    protected $guarded = ['id'];
}
