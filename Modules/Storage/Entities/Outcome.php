<?php

namespace Modules\Storage\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Outcome extends Model
{
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    protected $guarded = ['id'];
}
