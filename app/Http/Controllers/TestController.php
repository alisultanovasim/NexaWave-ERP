<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller
{
    public function test(){
        $user = User::where('id' , ">" , 3)->first();
        Mail::send('password.create' , ['user' => $user , 'password' => "asdasds"] , function ($m) {
            $m->from("support@ontime.az");
            $m->to('vusa.013@yandex.ru')->subject("One office create an account");
        });
        return 'success';
    }
}
