<?php


namespace App\Notification\Contracts;


interface Notification
{
    /**
     * Send Notification to one User
     * @return mixed
     */
    public function send();

    public function sendNow();

    public function save();

}
