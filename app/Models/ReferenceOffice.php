<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceOffice extends Model
{
    use SoftDeletes;
    protected $table='reference_offices';
    protected $fillable=['ref_name'];
}
