<?php

namespace Modules\Esd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Esd\Entities\senderCompany;
use Modules\Esd\Entities\senderCompanyRole;
use Modules\Esd\Entities\senderCompanyUser;

class HelperDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();


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
