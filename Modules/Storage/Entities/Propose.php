<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propose extends Model
{

    const STATUS_REJECTED=3;
    use SoftDeletes;

    public function proposeDocument()
    {
        return $this->belongsTo(ProposeDocument::class);
    }

    public function company()
    {
        return $this->belongsTo(ProposeCompany::class,'propose_company_id','id');
    }

    public function detail()
    {
        return $this->hasOne(ProposeDetail::class);
    }
}
