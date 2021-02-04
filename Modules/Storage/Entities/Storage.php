<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Storage extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function products(){
        return $this->hasMany(Product::class);
    }
}
