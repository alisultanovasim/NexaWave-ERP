<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;

class IsValidEmployeeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Employee::where([
            'id' => $value,
            'company_id' => $this->companyId
        ])->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'invalid user';
    }
}
