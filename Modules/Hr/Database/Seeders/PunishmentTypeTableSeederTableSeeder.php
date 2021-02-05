<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\PunishmentType;

class PunishmentTypeTableSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data = [
            [
                'id' => 1,
                'name' => 'Xəbərdarlıq',
                'position' => 1
            ],
            [
                'id' => 2,
                'name' => 'Töhmət',
                'position' => 2
            ],
            [
                'id' => 3,
                'name' => 'Şiddətli töhmət',
                'position' => 3
            ],
            [
                'id' => 4,
                'name' => 'Cərimə',
                'position' => 4
            ],
        ];

        foreach ($data as $item){
            PunishmentType::updateOrCreate(
                [
                    'id' => $item['id']
                ],
                [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'position' => $item['position'],
                ]
            );
        }
    }
}
