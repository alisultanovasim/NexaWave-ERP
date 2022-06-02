<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceOffice extends Model
{
    protected $table='reference_offices';
    protected $fillable=['ref_name'];
}
