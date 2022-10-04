<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposeCompany extends Model
{
    use SoftDeletes;
    protected $table='propose_companies';

    protected $fillable=[
      'company_name'
    ];

    public function datils()
    {
        return $this->hasMany(ProposeCompanyDetail::class);
    }

    public function proposes()
    {
        return $this->hasMany(Propose::class);
    }


}
