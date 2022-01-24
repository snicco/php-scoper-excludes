<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Snicco\PHPScoperWPExludes\FileDumper;

use function unlink;
use function is_dir;
use function is_file;

final class GlobalNamespaceTest extends TestCase
{
    
    private string $stub;
    private string $dump_to;
    
    protected function setUp() :void
    {
        parent::setUp();
        $this->stub = __DIR__.'/fixtures/wordpress-stubs.php';
        $this->dump_to = __DIR__.'/dump';
        $this->cleanDir();
    }
    
    protected function tearDown() :void
    {
        parent::tearDown();
        $this->cleanDir();
    }
    
    /** @test */
    public function functions_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-functions.php';
        
        $dumper = new FileDumper([$this->stub]);
        
        $this->assertFalse(is_file($expected_path));
        
        $dumper->dumpExludes($this->dump_to);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'foo',
            'bar',
        ], $functions);
    }
    
    /** @test */
    public function classes_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-classes.php';
        
        $dumper = new FileDumper([$this->stub]);
        
        $this->assertFalse(is_file($expected_path));
        
        $dumper->dumpExludes($this->dump_to);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'WP_User',
            'WP_Error',
        ], $classes);
    }
    
    /** @test */
    public function constants_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-constants.php';
        
        $dumper = new FileDumper([$this->stub]);
        
        $this->assertFalse(is_file($expected_path));
        
        $dumper->dumpExludes($this->dump_to);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'DB_NAME',
            'DB_USER',
        ], $classes);
    }
    
    /** @test */
    public function traits_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-traits.php';
        
        $dumper = new FileDumper([$this->stub]);
        
        $this->assertFalse(is_file($expected_path));
        
        $dumper->dumpExludes($this->dump_to);
        
        $this->assertTrue(is_file($expected_path));
        
        $classes = require_once $expected_path;
        
        $this->assertSame([
            'FooTrait',
            'BarTrait',
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