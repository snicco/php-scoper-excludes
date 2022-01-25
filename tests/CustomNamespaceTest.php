<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes\Tests;

use PhpParser\ParserFactory;
use InvalidArgumentException;
use PhpParser\Lexer\Emulative;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Snicco\PHPScoperWPExludes\ExclusionListGenerator;

use function touch;
use function unlink;
use function is_dir;
use function is_file;

final class CustomNamespaceTest extends TestCase
{
    
    private string                 $stub;
    private string                 $dump_to;
    private ExclusionListGenerator $dumper;
    
    protected function setUp() :void
    {
        parent::setUp();
        $this->stub = __DIR__.'/fixtures/wp-cli-stubs.php';
        $this->dump_to = __DIR__.'/dump';
        $this->cleanDir();
        $parser = (new ParserFactory())->create(
            ParserFactory::PREFER_PHP7,
            new Emulative(['phpVersion' => '8.0'])
        );
        $this->dumper = new ExclusionListGenerator($parser, $this->dump_to);
    }
    
    protected function tearDown() :void
    {
        parent::tearDown();
        $this->cleanDir();
    }
    
    /** @test */
    public function test_exception_for_bad_root_directory()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Directory [$this->dump_to/bogus] does not exist.");
        
        $parser = (new ParserFactory())->create(
            ParserFactory::PREFER_PHP7,
            new Emulative(['phpVersion' => '8.0'])
        );
        
        $d = new ExclusionListGenerator($parser, $this->dump_to.'/bogus');
    }
    
    /** @test */
    public function test_exception_for_dumping_bad_file()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("File [$this->dump_to/foo.php] is not readable.");
        
        $this->dumper->dumpForFile($this->dump_to.'/foo.php');
    }
    
    /** @test */
    public function test_exception_for_dumping_non_php_file()
    {
        touch($this->dump_to.'/foo.json');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Only PHP files can be processed.\nCant process file [$this->dump_to/foo.json]."
        );
        
        $this->dumper->dumpForFile($this->dump_to.'/foo.json');
    }
    
    /** @test */
    public function functions_in_the_custom_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wp-cli-functions.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpForFile($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'WP_CLI\\foo_func',
            'WP_CLI\\Utils\\wp_not_installed',
        ], $functions);
    }
    
    /** @test */
    public function classes_in_the_custom_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wp-cli-classes.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpForFile($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'WP_CLI\\Autoloader',
            'WP_CLI\\Bootstrap\\AutoloaderStep',
        ], $classes);
    }
    
    /** @test */
    public function interfaces_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wp-cli-interfaces.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpForFile($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'WP_CLI\\Bootstrap\\BootstrapInterface',
        ], $classes);
    }
    
    /** @test */
    public function constants_in_the_custom_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wp-cli-constants.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpForFile($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'FOO',
            'WP_CLI\\BAZ',
            'WP_CLI\\Utils\\BAM',
        ], $classes);
    }
    
    /** @test */
    public function traits_in_the_custom_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wp-cli-traits.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpForFile($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'WP_CLI\\FooTrait',
            'WP_CLI\\Bootstrap\\BarTrait',
        ], $classes);
    }
    
    private function cleanDir()
    {
        if ( ! is_dir($this->dump_to)) {
            return;
        }
        $files = Finder::create()->in($this->dump_to);
        foreach ($files as $file) {
            unlink($file->getRealPath());
        }
    }
    
}