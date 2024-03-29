<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita2c623b98a27b4b9de61f34091fae741
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Modules\\TaskManager\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Modules\\TaskManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita2c623b98a27b4b9de61f34091fae741::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita2c623b98a27b4b9de61f34091fae741::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
