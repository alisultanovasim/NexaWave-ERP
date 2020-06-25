<?php

use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            "Sənəd Dövriyyəsi" => [
                "sub_modules" => [
                    "Daxil olan sənədlər" => [
                        "sub_modules" => [],
                        "new_permissions" => [
                            "assign_create",
                            "assign_update",
                            "assign_delete",
                            "assign_read",
                            "change_status"
                        ]
                    ],
                    "Göndərilən sənədlər" => [
                        "sub_modules" => [],
                        "new_permissions" => [
                            "assign_create",
                            "assign_update",
                            "assign_delete",
                            "assign_read",
                            "change_status"
                        ]
                    ],
                    "Struktur daxili sənədlər" => [
                        "sub_modules" => [],
                        "new_permissions" => [
                            "assign_create",
                            "assign_update",
                            "assign_delete",
                            "assign_read",
                            "change_status"
                        ]
                    ],
                    "Arxiv sənədlər" => [
                        "sub_modules" => [],
                        "new_permissions" => []
                    ],
                    "Qaralamalar" => [
                        "sub_modules" => [],
                        "new_permissions" => []
                    ],
                    "Hesabat" => [
                        "sub_modules" => [],
                        "new_permissions" => []
                    ]
                ],
                "new_permissions" => [],
                'icon' =>  '/assets/images/svg/doc-management.svg'
            ],
            "Biznes Mərkəzinin İdarə Edilməsi" => [
                "new_permissions" => [],
                "sub_modules" => [
                    "Biznes Mərkəzinin İdarə Edilməsi" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Görüş otağı reservi" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Resepsiya" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Satış" => [
                        "new_permissions" => [
                            "floors_edit",
                            "floors_update",
                            "floors_read",
                            "floors_delete",
                            "card_edit",
                            "card_update",
                            "card_read",
                            "card_delete",
                            "offers_create",
                            "offers_update",
                            "offers_read",
                            "offers_delete",
                        ],
                        "sub_modules" => []
                    ],
                    "Qonaq siyahısı" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ]
                ],
                'icon' =>  '/assets/images/svg/marketing.svg'
            ],
            "İnsan Resursları" => [
                "sub_modules" => [
                    "İnzibatçı" => [
                        "sub_modules"=> [
                            "Səlahiyyətin rolları" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ],
                            "İstifadəçilər" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ],
                            "Cədvəllərin redaktə edilməsi" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ],
                            "Xəbərdarlıq" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ]
                        ],
                        "new_permissions" => []

                    ],
                    "Struktur" => [
                        "new_permissions" => [],
                        "sub_modules" => [
                            "Təşkilat və vəzifə bağlantıları" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ],
                            "Ştat cədvəli" => [
                                "new_permissions" => [],
                                "sub_modules" => []
                            ]
                        ]
                    ],
                    "Əməkdaşlar" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Müqavilələr" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Sənədlər" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "Hesabatlar" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ],
                    "İşin Uçotu" => [
                        "new_permissions" => [],
                        "sub_modules" => []
                    ]
                ],
                "new_permissions" => [],
                'icon' => '/assets/images/svg/hr.svg'
            ],
            "Maliyyə Hesabatı" => [
                "sub_modules" => [],
                "new_permissions" => [],
                'icon' => '/assets/images/svg/financial-report.svg'
            ],
            "Ehtiyatlar" => [
                "sub_modules" => [],
                "new_permissions" => [],
                'icon' => '/assets/images/svg/depo.svg'
            ],
            "Tapşırıqların İdarə Edilməsi" => [
                "sub_modules" => [],
                "new_permissions" => [],
                'icon' =>  '/assets/images/svg/project.svg'
            ],
            "Müqavilələr" => [
                "sub_modules" => [],
                "new_permissions" => [],
                'icon' =>  '/assets/images/svg/doc-management.svg'
            ]
        ];

//
        foreach ($modules as $key => $value) {
            $parent = \App\Models\Module::create([
                "name" => $key,
                "parent_id" => null
            ]);
            $this->create($value["sub_modules"], $parent->id);
            foreach ($value["new_permissions"] as $permission)
                \App\Models\Permission::create([
                    'module_id' => $parent->id,
                    "name" => $permission
                ]);
        }

    }

    public function create($module, $parent_id)
    {
        foreach ($module as $key => $value) {
            $parent = \App\Models\Module::create([
                "name" => $key,
                "parent_id" => $parent_id
            ]);
            $this->create($value["sub_modules"], $parent->id);

            foreach ($value["new_permissions"] as $permission ){
                \App\Models\Permission::create([
                    'module_id' => $parent->id,
                    "name" => $permission
                ]);
            }
        }

    }
}
