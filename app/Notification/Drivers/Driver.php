<?php


namespace App\Notification\Drivers;


use App\Notification\Contracts\Notification;
use App\Notification\Exceptions\NotificationException;
use Throwable;

/**
 * Class Driver
 * @package App\Notification\Drivers
 */
abstract class Driver implements Notification
{


    protected $is_success;

    /**
     * @var
     */
    protected $error;

    /**
     * @var
     */
    protected $driverName;

    /**
     * @var
     */
    protected $recipient = [];

    /**
     * @var
     */
    protected $application;

    /**
     * @var
     */
    protected $recipientList = [];

    /**
     * @var
     */
    protected $title;

    /**
     * @var array
     */
    protected array $data = [];


    /**
     * @var
     */
    protected $body;

    /**
     * @var
     */
    protected $notification;

    /**
     * @var array
     */
    protected array $localizationValues = [];

    /**
     * @var bool
     */
    protected $is_multilang = false;

    /**
     * @var
     */
    protected $lang;

    /**
     * @var
     */
    protected $to_application;


    /**
     * @return mixed
     */
    abstract public function send();

    abstract public function sendNow();

    /**
     * @return mixed
     */
    abstract public function save();


    /**
     * Set Service Defaults
     *
     */
    abstract public function setDefaults();

    /**
     * Set the recipient of the notification.
     *
     * @param string $recipient
     *
     * @return $this
     * @throws Throwable
     *
     */
    public function to(string $recipient): Driver
    {
        throw_if(is_null($recipient), new NotificationException('Recipients cannot be empty'));
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Set the title of the notification.
     *
     * @param string $title
     * @return $this
     * @throws Throwable
     */
    public function title(string $title): Driver
    {
        throw_if(empty($title), new NotificationException('Notification title is required'));
        $this->title = $title;
        return $this;
    }

    /**
     * @param array $list
     * @return $this
     * @throws Throwable
     */
    public function recipients(array $list): Driver
    {
//        throw_if(!count($list), new NotificationException('Recipients cannot be empty'));
        $this->recipientList = $list;
        return $this;
    }


    /**
     * @param mixed $application
     * @return Driver
     */
    public function application($application): Driver
    {
        $this->application = $application;
        return $this;
    }


    /**
     * @param mixed $body
     * @return Driver
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param mixed $localizationValues
     * @return Driver
     */
    public function localizationValues($localizationValues)
    {
        $this->localizationValues = $localizationValues;
        return $this;
    }

    /**
     * @return $this
     */
    public function isMultilang()
    {
        $this->is_multilang = true;
        return $this;
    }


    /**
     * @param mixed $lang
     * @return Driver
     */
    public function lang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param mixed $to_application
     * @return Driver
     */
    public function toApplication($to_application)
    {
        $this->to_application = $to_application;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSuccess()
    {
        return $this->is_success;
    }

    /**
     * @param mixed $is_success
     */
    public function setIsSuccess($is_success): void
    {
        $this->is_success = $is_success;
    }

    /**
     * @param mixed $data
     * @return Driver
     */
    public function setData(array $data): Driver
    {
        $this->data = $data;
        return $this;
    }


}
