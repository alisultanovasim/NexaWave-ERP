<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductAssignment extends Model
{
    protected $guarded = [];


    const ASSIGN_TO_USER = 1 ;
    const ASSIGN_TO_SECTION = 2 ;
}
