<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivateFile extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

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
    public static function allowedExtensions(): array {
        return [
            'txt'
        ];
    }
}
