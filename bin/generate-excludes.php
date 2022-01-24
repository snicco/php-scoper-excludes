<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$files = [
    dirname(__DIR__).'/vendor/php-stubs/wp-cli-stubs/wp-cli-stubs.php',
    //dirname(__DIR__).'/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
    //dirname(__DIR__).'/vendor/php-stubs/woocommerce-stubs/woocommerce-stubs.php',
    //dirname(__DIR__).'/vendor/php-stubs/woocommerce-stubs/woocommerce-packages-stubs.php',
    //dirname(__DIR__).'/vendor/php-stubs/wp-cli-stubs/wp-cli-commands-stubs.php',
    //dirname(__DIR__).'/vendor/php-stubs/wp-cli-stubs/wp-cli-i18n-stubs.php',
];

$dumper = new \Snicco\PHPScoperWPExludes\FileDumper($files);

$dumper->dumpExludes(dirname(__DIR__));
