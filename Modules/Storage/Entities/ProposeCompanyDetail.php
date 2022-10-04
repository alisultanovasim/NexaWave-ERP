<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposeCompanyDetail extends Model
{
    use SoftDeletes;
    protected $table='propose_company_details';
    protected $fillable=[
      'propose_company_id',
      'indicator',
      'value'
    ];

    public function proposeCompany()
    {
        return $this->belongsTo(ProposeCompany::class);
    }
}
