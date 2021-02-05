<?php

namespace Modules\Esd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Esd\Entities\Section;

class SectionTableSeeder extends Seeder
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






    }
}
