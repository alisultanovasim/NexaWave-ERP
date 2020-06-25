<?php

use Illuminate\Database\Seeder;

class ModulePermissionSeeder extends Seeder
{
    private $modulePermissions;

    public function __construct()
    {
        $this->modulePermissions = json_decode(file_get_contents('permissions.json'), true);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        dd($this->modulePermissions);
    }
}
