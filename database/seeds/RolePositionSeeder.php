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
            'super_admin',
            'employee',
            'office',
            'dev',
        ];
        foreach ($roles as $role)
            \App\Models\Role::create([
                'name' => $role
            ]);

    }
}
