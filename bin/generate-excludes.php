<?php

use Symfony\Component\Console\Application;
use Snicco\PhpScoperExcludes\Console\GenerateConfig;
use Snicco\PhpScoperExcludes\Console\GenerateExcludes;

$possible_autoload_paths = [
    // dependency
    dirname(__DIR__, 4).'/vendor/autoload.php',
    // local
    dirname(__DIR__).'/vendor/autoload.php',
];

$path = false;
foreach ($possible_autoload_paths as $possible_autoload_path) {
    if (is_file($possible_autoload_path)) {
        $path = $possible_autoload_path;
        break;
    }
}

if ( ! $path) {
    echo "Could not find vendor/autoload.php.\n";
    echo "Tried:\n";
    foreach ($possible_autoload_paths as $possible_autoload_path) {
        echo $possible_autoload_path;
        echo "\n";
    }
}
else {
    require_once $path;
}

$repository_root = str_replace('/vendor/autoload.php', '', $path);

$application = new Application();

$application->add(new GenerateConfig());
$application->add($default = new GenerateExcludes($repository_root));
$application->setDefaultCommand($default->getName());

$application->run();





