<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 1,
            'company_id' => 1,
            'sold_size' => 0.00
        ]);

        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 2,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 3,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 4,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 5,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 6,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 7,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 8,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 9,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);

        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 10,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
            'common_size' => 750.00,
            'number' => 11,
            'company_id' => 1,
            'sold_size' => 0.00

        ]);
        \Modules\Plaza\Entities\Floor::insert([
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




    }
}
