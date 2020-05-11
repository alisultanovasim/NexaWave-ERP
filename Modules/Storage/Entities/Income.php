<?php

namespace Modules\Storage\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $guarded = ['id'];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
