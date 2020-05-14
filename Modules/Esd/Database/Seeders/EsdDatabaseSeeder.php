<?php

namespace Modules\Esd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Esd\Entities\Section;
use Modules\Esd\Entities\senderCompany;
use Modules\Esd\Entities\senderCompanyRole;
use Modules\Esd\Entities\senderCompanyUser;

class EsdDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $arr = [
            [
                'name' => 'Kənar təşkilatlardan',
                'table' => 'structure_docs',
            ],
            [
                'name' => 'Vətəndas müraciətləri',
                'table' => 'citizen_docs',
            ],
            [
                'name' => 'Kənar təşkilatlara',
                'table' => 'structure_docs',
            ],
            [
                'name' => 'Vətəndaşlara',
                'table' => 'citizen_docs',
            ],
            [
                'name' => 'Structur daxili',
                'table' => 'in_company_docs',
            ],
        ];

        foreach ($arr as $a)
            Section::create([
                'name' => $a['name'],
                'table' => $a['table']
            ]);

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
//
        $sc =senderCompany::create([
            'name' => 'AzerSun MMC',
            'company_id' => 1
        ]);

        senderCompanyRole::insert([
            ['name' => 'CEO', 'sender_company_id' => $sc->id],
            ['name' => 'CTO', 'sender_company_id' => $sc->id]
        ]);

        senderCompanyUser::insert([
            ['name' => 'Gulnar Faganlı',
                'sender_company_id' => $sc->id,
                'sender_company_role_id' => 1],

            ['name' => 'Elvin Abbasov',
                'sender_company_id' =>  $sc->id,
                'sender_company_role_id' => 2]
        ]);

    }
}
