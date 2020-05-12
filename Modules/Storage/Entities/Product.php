<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    const STATE_NEW = 1;
    const STATE_OLD = 2;
    const STATE_OLD_USEFULL = 3;
    const STATE_OLD_USELESS = 4;
    const STATE_OLD_USELESS_ABLE_TO_FIX = 5;
    const STATE_OLD_USELESS_NOT_ABLE_TO_FIX = 6;

    public function kind(){
        return $this->belongsTo(ProductKind::class);
    }
    public function title(){
        return $this->belongsTo(ProductTitle::class);
    }

    public function state(){
        return $this->belongsTo(ProductState::class);
    }

    public function color(){
        return $this->belongsTo(ProductColor::class);
    }
}
