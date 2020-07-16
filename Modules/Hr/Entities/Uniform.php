<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\UniformType;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Uniform extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @return BelongsTo
     */
    public function uniformType(){
        return $this->belongsTo('Modules\Hr\Entities\UniformType', 'uniform_type_id', 'id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    /**
     * @param $query
     * @param $companyId
     * @return mixed
     */
    public function scopeCompanyId($query, $companyId){
        return $query->whereHas('employee.user', function ($query) use ($companyId){
           $query->company();
        });
    }
}
