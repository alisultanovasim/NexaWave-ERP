<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchiveRejectedPropose extends Model
{
    use SoftDeletes;
    protected $table='archive_rejected_proposes';
    protected $fillable=[
      'from_id',
      'reason',
      'propose_document_id',
    ];
}
