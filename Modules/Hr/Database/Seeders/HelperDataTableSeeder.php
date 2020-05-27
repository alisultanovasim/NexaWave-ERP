<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Section;
use Modules\Hr\Entities\Sector;

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






        $dep = Department::create([
            'name'=>'Informasiya texnologiyalari',
            'short_name' => 'IT',
            'code' => 1,
            'country_id' => 1,
            'city_id' => 1,
            'position' => 1,
            'company_id' => 1,
        ]);

        $section =   Section::create([
            'department_id' => $dep->id,
            'name' => 'Programlasdirma' ,
            'short_name' => 'Programlasdirma' ,
            'code' => '1' ,
            'position' => 1 ,
            'company_id' => 1 ,
        ]);

        $sector = Sector::create([
            'section_id' =>$section->id ,
            'name' => 'Banck-end',
            'short_name' => 'back-end',
            'code' => 1,
            'position' =>1 ,
            'company_id' => 1,
        ]);
    }
}
