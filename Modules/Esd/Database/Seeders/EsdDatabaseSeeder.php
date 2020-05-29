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

        $this->call(EsdRegionsTableSeeder::class);
        $this->call(SectionTableSeeder::class);
        $this->call(SendFormAndTypeTableSeeder::class);
        $this->call(HelperDataTableSeeder::class);
    }
}
