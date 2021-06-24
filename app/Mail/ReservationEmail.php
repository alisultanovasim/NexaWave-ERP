<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $company;

    private $date;

    private $room;

    private $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $company, $date, $room)
    {
        $this->company = $company;
        $this->date = $date;
        $this->room = $room;
        $this->email = $email;
        \Log::info("Email queued");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Log::info("email is builded");
        return $this->view('emails.reservation')->with([
            'company' => $this->company,
            "date" => $this->date,
            'room' => $this->room
        ])->to($this->email);
    }
}
