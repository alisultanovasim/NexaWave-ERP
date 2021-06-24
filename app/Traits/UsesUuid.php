<?php


namespace App\Traits;


use Illuminate\Support\Str;

trait UsesUuid
{
    protected static function bootUsesUuid() {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * @return false
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }
}
