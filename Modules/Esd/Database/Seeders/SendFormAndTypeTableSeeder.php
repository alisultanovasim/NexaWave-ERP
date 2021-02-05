<?php

namespace Modules\Esd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SendFormAndTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();


        \Illuminate\Support\Facades\DB::table('send_types')
            ->insert([
                ['name' => 'Kuryer'],
                ['name' => 'Poçt'],
                ['name' => 'Faks'],
                ['name' => 'E-mail'],
            ]);

        \Illuminate\Support\Facades\DB::table('send_forms')
            ->insert([
                ['name' => 'Şikayət'],
                ['name' => 'Təklif'],
                ['name' => 'Müraciət'],
                ['name' => 'Təbrik'],
            ]);

        // $this->call("OthersTableSeeder");
    }
}
