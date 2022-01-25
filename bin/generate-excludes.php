<?php

use Symfony\Component\Console\Application;
use Snicco\PhpScoperExcludes\Console\GenerateConfig;
use Snicco\PhpScoperExcludes\Console\GenerateExcludes;

autoload();

$application = new Application();

$application->add(new GenerateConfig());
$application->add($default = new GenerateExcludes());
$application->setDefaultCommand($default->getName());

$application->run();

function autoload()
{
    $possibleAutoloadPaths = [
        // dependency
        dirname(__DIR__, 3).'/vendor/autoload.php',
        // local
        dirname(__DIR__).'/vendor/autoload.php',
    ];
    
    $found = false;
    foreach ($possibleAutoloadPaths as $possible_autoload_path) {
        if (is_file($possible_autoload_path)) {
            $found = true;
            require_once $possible_autoload_path;
        }
    }
    
    if (false === $found) {
        echo "Could not find vendor/autoload.php.\n";
        echo "Tried:\n";
        foreach ($possibleAutoloadPaths as $possible_autoload_path) {
            echo $possible_autoload_path;
            echo "\n";
        }
    }
}



