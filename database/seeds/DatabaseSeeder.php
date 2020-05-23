<?php

use Illuminate\Database\Seeder;
use Modules\Plaza\Entities\Kind;

class DatabaseSeeder extends Seeder
{


    public function run()
    {

        $this->call(PermissionSeeder::class);
        $this->call(ModulesSeeder::class);
        $this->call(RolePositionSeeder::class);
        $this->call(UserSeeder::class);

    }


}
