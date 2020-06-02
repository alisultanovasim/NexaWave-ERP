<?php

namespace App\Http\Controllers;


class TestController extends Controller
{
    public function test(){
        $this->authorize('edit-test');
        dd(auth()->user()->getUserRolesForRequest());
        return 'a';
    }
}
