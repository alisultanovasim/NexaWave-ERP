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
//


        $title = ProductTitle::create([
            'company_id' => 1,
            'name' => 'Elektrotexnika'
        ]);

        $kind = ProductKind::create([
            'title_id' => $title->id,
            'name' => 'Kompyuter',
            'company_id' => 1,
            'unit_id' => 1
        ]);

        Storage::create([
            'company_id' => 1,
            'name' => '28 time plaza',
            'size' => 170.00
        ]);
//
        Product::create([
            "company_id" => 1,
            "unit_id" => 1,
            "less_value" => true,
            "quickly_old" => false,
            "title_id" => $title->id,
            "kind_id" => $kind->id,
            "state_id" => 1,
            "description" => 'About: 16 ram  , 156 gb , i7',
            "amount" => 2,
            "storage_id" => 1,
            "product_model" => 'Apple',
            "product_mark" => 'Mac book pro',
            "product_no" => '123456789',
            "color_id" => 1,
            "main_funds" => true,
            "inv_no" => 'ik-16-265',
            "exploitation_date" => '2025-05-13',
            "size" => 1600,
            "made_in_country" => 1,
            "buy_from_country" => 1,
            "make_date" => '2014-05-13',
            'status' => 1,
            'initial_amount'=>2
        ]);
    }
}



