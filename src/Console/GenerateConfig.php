<?php

declare(strict_types=1);

namespace Snicco\PhpScoperExcludes\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function getcwd;
use function dirname;
use function file_get_contents;
use function file_put_contents;

final class GenerateConfig extends Command
{
    
    protected static $defaultName        = 'generate-config';
    protected static $defaultDescription = 'Generate a new configuration file in the current working directory.';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title("Generating configuration in current working directory.");
        
        $contents = file_get_contents(dirname(__DIR__).'/stubs/generate-excludes.inc.php');
        
        if (false === $contents) {
            throw new RuntimeException("Cant read included configuration...");
        }
        
        $path = getcwd().'/generate-excludes.inc.php';
        
        $success = file_put_contents($path, $contents);
        
        if (false === $success) {
            throw new RuntimeException("Could not write the configuration to file [$path].");
        }
        
        $io->success("Generated config at [$path].");
        
        return Command::SUCCESS;
    }
    
}