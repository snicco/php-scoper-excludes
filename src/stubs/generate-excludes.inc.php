<?php

declare(strict_types=1);

use PhpParser\ParserFactory;
use Snicco\PhpScoperExcludes\Option;

return [
    Option::EMULATE_PHP_VERSION => Option::PHP_8_0,
    // use the current working directory
    Option::OUTPUT_DIR => null,
    // pass files as command arguments
    Option::FILES => [],
    
    Option::PREFER_PHP_VERSION => ParserFactory::PREFER_PHP7,
];