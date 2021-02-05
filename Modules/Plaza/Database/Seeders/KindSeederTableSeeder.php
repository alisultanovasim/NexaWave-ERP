<?php

namespace Modules\Plaza\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class KindSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \Modules\Plaza\Entities\Kind::insert([
            ['title' => 'Şikayət'],
            ['title' => 'Təbrik'],
            ['title' => 'Etiraz'],
            ['title' => 'Digər'],
        ]);
    }
}
