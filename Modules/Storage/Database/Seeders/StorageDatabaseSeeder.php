<?php

namespace Modules\Storage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Storage\Entities\Unit;

class StorageDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
        $arr = [
            [
                "name" => "kq",
                "full_name" => "Kilogram",
                "column_type" => "int"
            ],
        ];
        foreach ($arr as $a){
            Unit::create($a);
        }
    }
}
