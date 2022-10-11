<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class ArchivePropose extends Model
{
    use SoftDeletes;
    protected $table='archive_rejected_proposes';
    protected $fillable=[
      'from_id',
      'reason',
      'propose_document_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'from_id');
    }
}
