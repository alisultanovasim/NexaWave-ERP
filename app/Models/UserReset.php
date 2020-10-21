<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class UserReset extends Model
{
    use SoftDeletes;

    protected $fillable = [
       'email', 'hash', 'user_ip', 'user_agent', 'expire_date', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @var int
     */
    private $dailyResetLimit = 3;

    /**
     * With seconds
     * @var int
     */
    private $expireTime = 180;

    /**
     * @param $query
     * @return mixed
     */
    public function scopeToday($query){
        return $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNotExpired($query){
        return $query->where('expire_date', '>=', Carbon::now());
    }

    /**
     * @return int
     */
    public function getDailyResetCount(){
        return $this->dailyResetLimit;
    }


    /**
     * @return String
     */
    public function getRandomHash(){
        return Str::uuid();
    }

    /**
     * @return int
     */
    public function getExpireTime(){
        return $this->expireTime;
    }
}
