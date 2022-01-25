<?php

declare(strict_types=1);

namespace Snicco\PhpScoperExcludes\Console;

use PhpParser\ParserFactory;
use PhpParser\Lexer\Emulative;
use Snicco\PhpScoperExcludes\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Snicco\PhpScoperExcludes\ExclusionListGenerator;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function getcwd;
use function is_file;
use function gettype;
use function sprintf;
use function is_array;
use function basename;
use function is_string;
use function array_map;
use function is_iterable;
use function array_merge;
use function array_values;
use function iterator_to_array;

final class GenerateExcludes extends Command
{
    
    protected static $defaultName = 'run';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title("Generating exclusion lists.");
        
        $config = getcwd().'/generate-excludes.inc.php';
        if ( ! is_file($config)) {
            $io->error([
                "Configuration file not found at path [$config].",
                'Please run "vendor/bin/generate-excludes generate-config"',
            ]);
            
            return Command::FAILURE;
        }
        
        $config = require_once $config;
        $files = $config[Option::FILES];
        
        if ( ! is_array($files)) {
            $io->error([
                'Option::Files has to be an array of string|iterable.',
                'Got:'.gettype($files),
            ]);
            return Command::FAILURE;
        }
        
        $io->note("Normalizing files...");
        
        $files = $this->normalizeFiles($files);
        $count = count($files);
        
        if ( ! $count) {
            $io->note('No files found. Aborting...');
            return Command::SUCCESS;
        }
        
        $io->info(
            sprintf(
                '%s %s found. Starting to generate excludes...',
                $count,
                $count > 1 ? 'files' : 'file'
            )
        );
        
        $generator = $this->newGenerator(
            $config,
            $output_dir = $config[Option::OUTPUT_DIR] ?? getcwd()
        );
        
        $progress_bar = $this->newProgressBar($output, $count);
        $progress_bar->setMessage(basename($files[0]));
        $progress_bar->start();
        
        foreach ($files as $file) {
            $progress_bar->setMessage(basename($file));
            $generator->dumpForFile($file);
            $progress_bar->advance();
        }
        
        $progress_bar->finish();
        
        $io->newLine(2);
        $io->success(
            sprintf(
                "Generated exclusion list for %s %s in directory %s.",
                count($files),
                count($files) > 1 ? 'files' : 'file',
                $output_dir
            )
        );
        
        return Command::SUCCESS;
    }
    
    private function newGenerator(array $config, string $output_dir) :ExclusionListGenerator
    {
        $prefer = $config[Option::PREFER_PHP_VERSION] ?? ParserFactory::PREFER_PHP7;
        $emulate_version = $config[Option::EMULATE_PHP_VERSION] ?? Option::PHP_8_0;
        
        $lexer = new Emulative(['phpVersion' => $emulate_version]);
        
        $parser = (new ParserFactory())->create($prefer, $lexer);
        
        return new ExclusionListGenerator($parser, $output_dir);
    }
    
    /**
     * @return string[]
     */
    private function normalizeFiles(array $files) :array
    {
        $_files = [];
        
        foreach ($files as $file) {
            if (is_string($file)) {
                $_files = array_merge($_files, [$file]);
                continue;
            }
            
            if (is_iterable($file)) {
                $_files = array_merge($_files, iterator_to_array($file));
            }
        }
        return array_values(array_map(fn($file) => (string) $file, $_files));
    }
    
    private function newProgressBar(OutputInterface $output, int $file_count) :ProgressBar
    {
        $progress_bar = new ProgressBar($output, $file_count);
        $progress_bar->setFormat(
            ' %current%/%max% [%bar%] <info>%percent%%</info> %elapsed:6s% <info>(%message%)</info>'
        );
        $progress_bar->setRedrawFrequency(100);
        $progress_bar->maxSecondsBetweenRedraws(0.2);
        $progress_bar->minSecondsBetweenRedraws(0.2);
        
        return $progress_bar;
    }
    
}