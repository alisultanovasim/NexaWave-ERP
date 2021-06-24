<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

/**
 * @property string $id
 * @property string $media_id
 * @property string $title
 * @property string $body
 * @property mixed $data
 * @property string $language
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 */
class Notification extends Model
{
    use UsesUuid;

    protected $casts = [
        'data' => 'json'
    ];
    /**
     * @var array
     */
    protected $fillable = ['id', 'title', 'body', 'data', 'media_id', 'language', 'type', 'created_at', 'updated_at'];

}
