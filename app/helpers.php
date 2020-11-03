<?php

use Illuminate\Support\Str;

if (!function_exists('createNewPhotoName')) {

    function createNewPhotoName(string $ext): string
    {
        return strtolower(Str::random(10) . '_' . time() . '.' . $ext);
    }
}
