<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class User extends  Model
{
    protected $table = 'users';

    protected  $connection = 'mysql_hr';

}
