<?php

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = ["create" , 'read','update' , 'delete'];
        foreach ($permissions as $permission)
            \App\Models\Permission::create([
                "name" => $permission
            ]);


    }
}
