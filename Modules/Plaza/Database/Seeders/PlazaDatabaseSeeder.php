<?php

namespace Modules\Plaza\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Plaza\Entities\Floor;
use Modules\Plaza\Entities\Location;

class PlazaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 1,
            'company_id' => 1,
            'sold_size' => 0.00
        ]);

        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 2,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 3,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 4,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 5,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 6,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 7,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 8,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 9,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 10,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 11,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::create([
            'common_size' => 750.00,
            'number' => 12,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Kind::insert([
            ['title' => 'Şikayət'],
            ['title' => 'Təbrik'],
            ['title' => 'Etiraz'],
            ['title' => 'Digər'],
        ]);

        $office = \Modules\Plaza\Entities\Office::create([
            'company_id' => 1,
            'name' => "Timesoft",
            "entity" => 1,
            'agree_at' => '2020-05-05',
            'voen' => '1234567',
            'per_month' => 700.50,
            'description' => 'some text',
            'start_time' => '2020-03-05',
            'month_count' => 12,
            'payed_month_count' => 2

        ]);

        Location::create([
            'size' => 10.1,
           'floor_id' =>1,
           'office_id' => $office->id
        ]);

        // $this->call("OthersTableSeeder");
    }
}
