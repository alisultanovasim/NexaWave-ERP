<?php

namespace Modules\Storage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductState;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\Storage;
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

        $arr = [
            [
                "name" => "kq",
                "full_name" => "Kilogram",
                "column_type" => "int"
            ],
        ];

        foreach ($arr as $a) {
            Unit::create($a);
        }

        $arr = [
            "Təzə",
            "Köhnə yararlı",
            "Köknə yararsiz",
            "Təmirə ehtiyaci olan",
        ];

        foreach ($arr as $a) {
            ProductState::create([
                "name" => $a
            ]);
        }
    }
}



