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

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function build()
    {
        return $this->view('mail')
            ->with([
                'username' => $this->user->username,
                'password' => $this->user->password,
                'name' => $this->user->name,
                'surname' => $this->user->surname
            ]);
    }
}
