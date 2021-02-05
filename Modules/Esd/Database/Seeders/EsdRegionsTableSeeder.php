<?php

namespace Modules\Esd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EsdRegionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        \Illuminate\Support\Facades\DB::table('esd_regions')->insert([
            ['name' => 'Qaradağ'],
            ['name' => 'Binəqədi'],
            ['name' => 'Nizami'],
            ['name' => 'Nərimanov'],
            ['name' => 'Nəsimi'],
            ['name' => 'Pirallahı'],
            ['name' => 'Sabunçu'],
            ['name' => 'Səbail'],
            ['name' => 'Suraxanı'],
            ['name' => 'Xətai'],
            ['name' => 'Xəzər'],
            ['name' => 'Yasamal'],
        ]);
    }
}
