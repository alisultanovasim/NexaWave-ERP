<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\BloodGroup;
use Modules\Hr\Entities\Color;
use Modules\Hr\Entities\Currency;
use Modules\Hr\Entities\DurationType;

class CurrencyColorBloodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();


        DurationType::insert([
            ['name' => 'Müddətli'],
            ['name' => 'Müddətsiz'],
        ]);

        Currency::create([
            'char' => '$',
            'name' => 'aze',
            'code' => 1231,
        ]);

//
        BloodGroup::insert([
            ['name' => 'O(I)RH+'],
            ['name' => 'O(I)RH-'],
            ['name' => 'A(II)RH+'],
            ['name' => 'A(II)RH-'],
            ['name' => 'B(III)RH+'],
            ['name' => 'B(III)RH-'],
            ['name' => 'AB(IV)RH+'],
            ['name' => 'AB(IV)RH-'],
        ]);
    }
}
