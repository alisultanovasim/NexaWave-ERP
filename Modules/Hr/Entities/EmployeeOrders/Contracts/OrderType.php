<?php


namespace Modules\Hr\Entities\EmployeeOrders\Contracts;


interface OrderType
{
    public function getEmployeeValidateRules(): array;
}
