<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class PrivateFile extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = ['id'];

    //with MB
    private $maxSize = 5;

    protected $casts = [
        'file' => 'json'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeCompany($query){
        return $query->whereHas('user', function ($query) {
            $query->company();
        });
    }

    /**
     * Add extensions to validate files
     * @return array|string[]
     */
    public function allowedExtensions(): array {
        return ['txt', 'jpg', 'jpeg', 'png', 'svg', 'pdf', 'doc', 'docx', 'csv', 'xls', 'xlsx'];
    }

    public function getMaxSizeField(){
        return with(new static())->maxSize;
    }
}
