<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);


        $roles = [
            'super_admin',
            'employee',
            'office',
            'dev'
        ];
        foreach ($roles as $role)
        \App\Models\Role::create([
            'name' => $role
        ]);
    }
}
