<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Currency;

class CurrencyTableSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $currencies = [
            [
                'id' => 1,
                'name' => 'AZN',
                'code' => 994
            ],
            [
                'id' => 2,
                'name' => 'RUB',
                'code' => null
            ],
            [
                'id' => 3,
                'name' => 'USD',
                'code' => null
            ],
            [
                'id' => 4,
                'name' => 'EUR',
                'code' => null
            ],
            [
                'id' => 5,
                'name' => 'GBP',
                'code' => null
            ],
        ];

        foreach ($currencies as $currency){
            Currency::updateOrCreate(
                [
                    'id' => $currency['id']
                ],
                [
                    'id' => $currency['id'],
                    'name' => $currency['name'],
                    'code' => $currency['code'],
                ]
            );
        }
    }
}
