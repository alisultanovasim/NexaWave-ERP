<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchiveRejectedPurchase extends Model
{
    use SoftDeletes;

    protected $table='archive_rejected_purchases';
    protected $fillable=['from_id','purchase_id','reason','is_active'];
}
