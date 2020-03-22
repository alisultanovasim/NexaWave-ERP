<?php

namespace Modules\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inbox extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];
    public $timestamps = false;
}
