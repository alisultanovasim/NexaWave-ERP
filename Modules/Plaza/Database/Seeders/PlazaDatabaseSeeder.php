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
         $this->call(KindSeederTableSeeder::class);
//        $this->call(HelperDataSeederTableSeeder::class);
    }
}
