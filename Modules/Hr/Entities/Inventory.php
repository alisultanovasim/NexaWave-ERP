<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Inventory extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @return BelongsTo
     */
    public function inventoryType(){
        return $this->belongsTo('Modules\Hr\Entities\InventoryType', 'inventory_type_id', 'id');
    }

    /**
     * @param $query
     * @param $companyId
     * @return mixed
     */
    public function scopeCompanyId($query, $companyId){
        return  $query->whereHas('inventoryType', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
        });
    }
}
