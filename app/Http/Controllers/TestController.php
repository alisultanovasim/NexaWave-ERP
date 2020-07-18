<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function test(){
//        dd(Auth::user()->can('edit,test'));
        return 'a';
    }
}
