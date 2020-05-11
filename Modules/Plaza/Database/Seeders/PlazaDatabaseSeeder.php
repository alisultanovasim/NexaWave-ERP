<?php

namespace Modules\Plaza\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Plaza\Entities\Floor;

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


        Floor::create([
            'number' => 1,
            'common_size'=>200.00,
            'company_id'=>1
        ]);
        // $this->call("OthersTableSeeder");
    }
}
