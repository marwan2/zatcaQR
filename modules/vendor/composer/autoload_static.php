<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit513c8a9f4fda35aaea73e152c3c619ed
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'chillerlan\\Settings\\' => 20,
            'chillerlan\\QRCode\\' => 18,
        ),
        'S' => 
        array (
            'Salla\\ZATCA\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'chillerlan\\Settings\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-settings-container/src',
        ),
        'chillerlan\\QRCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-qrcode/src',
        ),
        'Salla\\ZATCA\\' => 
        array (
            0 => __DIR__ . '/..' . '/salla/zatca/src',
        ),
    );

    public static $classMap = array (
        'API\\Helpers\\Helper' => __DIR__ . '/../..' . '/apiv2/Helpers/Helper.php',
        'API\\Zatca\\ZatcaQR' => __DIR__ . '/../..' . '/apiv2/Zatca/ZatcaQR.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit513c8a9f4fda35aaea73e152c3c619ed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit513c8a9f4fda35aaea73e152c3c619ed::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit513c8a9f4fda35aaea73e152c3c619ed::$classMap;

        }, null, ClassLoader::class);
    }
}
