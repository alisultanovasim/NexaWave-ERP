<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $organization_type_id
 * @property integer $assigned_to
 * @property integer $country_id
 * @property integer $city_id
 * @property integer $region_id
 * @property integer $bank_information_id
 * @property string $code
 * @property string $name
 * @property string $short_name
 * @property boolean $is_head
 * @property string $phone
 * @property string $fax
 * @property string $address
 * @property string $email
 * @property string $website
 * @property string $post_code
 * @property string $profession
 * @property boolean $is_closed
 * @property string $closed_date
 * @property string $note
 * @property integer $index
 * @property integer $company_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Organization $assigned
 * @property BankInformation $bankInformation
 * @property City $city
 * @property Country $country
 * @property OrganizationType $organizationType
 * @property Region $region
 */
class Organization extends Model
{
    use SoftDeletes;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';


    /**
     * @var array
     */
    protected $fillable = ['organization_type_id', 'assigned_to', 'country_id', 'city_id', 'region_id', 'bank_information_id', 'code', 'name', 'short_name', 'is_head', 'phone', 'fax', 'address', 'email', 'website', 'post_code', 'profession', 'is_closed', 'closed_date', 'note', 'index', 'company_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return BelongsTo
     */
    public function assigned()
    {
        return $this->belongsTo('Modules\Hr\EntitiesOrganization', 'assigned_to');
    }

    /**
     * @return BelongsTo
     */
    public function bankInformation()
    {
        return $this->belongsTo('Modules\Hr\EntitiesBankInformation');
    }

    /**
     * @return BelongsTo
     */
    public function city()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCity');
    }

    /**
     * @return BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCountry');
    }

    /**
     * @return BelongsTo
     */
    public function organizationType()
    {
        return $this->belongsTo('Modules\Hr\EntitiesOrganizationType');
    }

    /**
     * @return BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('Modules\Hr\EntitiesRegion');
    }
}
