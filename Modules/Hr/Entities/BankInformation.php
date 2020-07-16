<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property string $registration_date
 * @property string $licence
 * @property string $auditor
 * @property string $address
 * @property string $phone
 * @property string $correspondent
 * @property string $swift
 * @property string $code
 * @property string $teleks
 * @property int $fax
 * @property string $email
 * @property string $site
 * @property string $voen
 * @property integer $index
 * @property string $created_at
 * @property string $updated_at
 */
class BankInformation extends Model
{
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bank_information';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['company_id' , 'name', 'short_name', 'registration_date', 'licence', 'auditor', 'address', 'phone', 'correspondent', 'swift', 'code', 'teleks', 'fax', 'email', 'site', 'voen', 'index', 'created_at', 'updated_at'];

}
