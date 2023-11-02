<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit51df6ce1cd4c7758bb01ff291a9a477f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit51df6ce1cd4c7758bb01ff291a9a477f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit51df6ce1cd4c7758bb01ff291a9a477f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit51df6ce1cd4c7758bb01ff291a9a477f::$classMap;

        }, null, ClassLoader::class);
    }
}
