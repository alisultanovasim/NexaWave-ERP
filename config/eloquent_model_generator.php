<?php

use Illuminate\Database\Eloquent\Model;

return [
    'model_defaults' => [
        'namespace' => 'Modules\\TaskManager\\Entities',
        'base_class_name' => Model::class,
        'output_path' => base_path("Modules/TaskManager/Entities/"),
        'no_timestamps' => null,
        'date_format' => null,
        'connection' => null,
        'backup' => null,
    ],
    'db_types' => [
        'enum' => 'string',
    ],
];
