<?php

use App\Models\PositionModulePermission;
use Illuminate\Database\Seeder;

class PositionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PositionModulePermission::insert([
            "id" => "123e4a67-e89b-12d3-a456-426614174000",
            'module_id' => 1,
            'permission_id' => 1 ,
            'position_id'=>2
        ]);
    }
}
