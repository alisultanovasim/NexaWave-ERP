<?php


namespace App\Services;


use App\Http\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use App\Notification\Models\NotificationToken;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class NotificationService
 * @package App\Services
 */
class NotificationService
{


    /**
     * @var NotificationToken
     */
    private $notificationToken;

    /**
     * NotificationService constructor.
     * @param NotificationToken $notificationToken
     */
    public function __construct( NotificationToken $notificationToken)
    {
        $this->notificationToken = $notificationToken;
    }

    /**
     * @param $data
     */
    public function registerFCMToken($data): void
    {
        $this->notificationToken->fill($data)->save();
    }

    /**
     * @param $notificationToken
     */
    public function set($notificationToken): void
    {
        $this->notificationToken = $notificationToken;
    }


}
