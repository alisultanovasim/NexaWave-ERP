<?php


namespace App\Notification\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Interface NotificationFacade
 * @package App\Notification\Facades
 */
class NotificationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'notification';
    }


}
