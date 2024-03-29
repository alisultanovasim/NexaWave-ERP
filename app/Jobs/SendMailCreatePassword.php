<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailCreatePassword implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    public $tries = 5;
    public $password;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data , $password)
    {
        $this->user = $data;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         Mail::send('password.create' , ['user' => $this->user , 'password' => $this->password] , function ($m) {
            $m->from("Info@1of.az");
            $m->to($this->user->email)->subject("One office create an account");
        });

    }
}
