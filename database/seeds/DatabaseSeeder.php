<?php

use Illuminate\Database\Seeder;
use Modules\Plaza\Entities\Kind;

class DatabaseSeeder extends Seeder
{


    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(ModulesSeeder::class);

        $kinds = ['Təbrik','Şikayət','Təklif',];
        foreach ($kinds as $kind)
            Kind::create([
                'title' => $kind
            ]);
        \Modules\Hr\Entities\Language::create([
            "name" => "Azerbaycan",
            "code" => "AZE",
            "iso" => "AZ"
        ]);
    }


}
