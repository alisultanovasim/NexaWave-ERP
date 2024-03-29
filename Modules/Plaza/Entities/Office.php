<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    const PARKING_UNDERGROUND = 1;
    const PARKING_ABOVE_GROUND = 2;


    protected $guarded = ['id'];

    public function location()
    {
        return $this->hasMany('Modules\Plaza\Entities\Location', 'office_id', 'id');
    }

    public function contract()
    {
        return $this->hasOne('Modules\Plaza\Entities\Contract', 'office_id', 'id');
    }

    public function contact()
    {
        return $this->hasMany('Modules\Plaza\Entities\Contact');
    }

    public function workers()
    {
        return $this->hasMany('Modules\Plaza\Entities\Workers', 'office_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('Modules\Plaza\Entities\OfficeUser');
    }

    public function documents()
    {
        return $this->hasMany('Modules\Plaza\Entities\Document');
    }

    public function getImageAttribute($value)
    {
        if ($value)
            return config('app.url') . '/storage/' . $value;
        return $value;
    }

}
