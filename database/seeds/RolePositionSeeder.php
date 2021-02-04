<?php

use Illuminate\Database\Seeder;

class RolePositionSeeder extends Seeder
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
            [
                'id' => 1,
                'name' => 'super_admin',
            ],
            [
                'id' => 2,
                'name' => 'employee'
            ],
            [
                'id' => 3,
                'name' => 'office_admin'
            ],
            [
                'id' => 4,
                'name' => 'dev'
            ],
            [
                'id' => 5,
                'name' => 'company_admin'
            ]
        ];
        foreach ($roles as $role)
            \App\Models\Role::create([
                'id' => $role['id'],
                'name' => $role['name']
            ]);
    }
}
