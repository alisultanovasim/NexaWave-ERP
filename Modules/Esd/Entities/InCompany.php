<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class InCompany extends Model
{

    protected  $table = 'in_company_docs';

    public $timestamps = false;

    protected  $guarded = ['id'];

}
