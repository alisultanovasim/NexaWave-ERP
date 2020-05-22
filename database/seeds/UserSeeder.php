<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Modules\Hr\Entities\Positions::create([
            'name' => 'Director',
            'short_name' => 'Director'
        ]);
        $roles = [
            'super_admin',
            'employee',
            'office',
            'dev',
        ];
        foreach ($roles as $role)
            \App\Models\Role::create([
                'name' => $role
            ]);

        $vusal = \App\Models\User::create([
            'username' => 'vusal123',
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'email' => 'vusal@mail.ru',
            'role_id' => \App\Models\User::EMPLOYEE,
            'name' => 'vusal',
        ]);

        $zeka = \App\Models\User::create([
            'username' => 'zeka',
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'email' => 'zeka@mail.ru',
            'role_id' => \App\Models\User::EMPLOYEE,
            'name' => 'Zeka',
        ]);

        $company = \App\Models\Company::create([
            'name' => 'TimeSoft'
        ]);


        $zekaEmp = \Modules\Hr\Entities\Employee\Employee::create([
            'user_id' => $zeka->id,
            'company_id' => $company->id,
        ]);

        $vusalEmp = \Modules\Hr\Entities\Employee\Employee::create([
            'user_id' => $vusal->id,
            'company_id' => $company->id,
        ]);
        $position = \Modules\Hr\Entities\Positions::create([
            'name' => 'Php developer',
            'short_name' => 'backend',
            'company_id' => $company->id
        ]);

        \Modules\Hr\Entities\Employee\Contract::create([
            'salary' => 1500,
            'position_id' => $position->id,
            'employee_id' => $vusalEmp->id
        ]);

        \Modules\Hr\Entities\Employee\Contract::create([
            'salary' => 1500,
            'position_id' => $position->id,
            'employee_id' => $zekaEmp->id
        ]);

        \Modules\Hr\Entities\Employee\UserDetail::create([
            'user_id' => $zeka->id,
            'fin' => $zeka->username,
            'gender' => 'm'
        ]);
        \Modules\Hr\Entities\Employee\UserDetail::create([
            'user_id' => $vusal->id,
            'fin' => $vusal->username,
            'gender' => 'm'
        ]);

    }
}
