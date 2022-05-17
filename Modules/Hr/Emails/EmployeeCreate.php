<?php

namespace Modules\Hr\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmployeeCreate extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $pass;

    public function __construct(User $user,$pass)
    {
        $this->user = $user;
        $this->pass=$pass;
    }


    public function build()
    {
        return $this->view('mail')
            ->with([
                'username' => $this->user->username,
                'password' => $this->pass,
                'name' => $this->user->name,
                'surname' => $this->user->surname
            ]);
    }
}
