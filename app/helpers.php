<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Hr\Entities\Employee\Employee;

if (!function_exists('createNewPhotoName')) {

    function createNewPhotoName(string $ext): string
    {
        return strtolower(Str::random(10) . '_' . time() . '.' . $ext);
    }
}
