<?php

namespace App\Http\Controllers;


class TestController extends Controller
{
    public function test(){
        $this->authorize('create-test');
        return 'a';
    }
}
