<?php

declare(strict_types=1);

namespace Snicco\PhpScoperExcludes\Tests;

use PhpParser\ParserFactory;
use PhpParser\Lexer\Emulative;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Snicco\PhpScoperExcludes\ExclusionListGenerator;

use function is_dir;
use function unlink;
use function is_file;
use function json_decode;
use function file_get_contents;

final class GlobalNamespaceTest extends TestCase
{
    
    private string                 $stub;
    private string                 $dump_to;
    private ExclusionListGenerator $dumper;
    private string                 $fixtures_dir;
    
    protected function setUp() :void
    {
        parent::setUp();
        $this->fixtures_dir = __DIR__.'/fixtures';
        $this->stub = __DIR__.'/fixtures/wordpress-stubs.php';
        $this->dump_to = __DIR__.'/dump';
        
        $parser = (new ParserFactory())->create(
            ParserFactory::PREFER_PHP7,
            new Emulative(['phpVersion' => '8.0'])
        );
        $this->dumper = new ExclusionListGenerator($parser, $this->dump_to);
        
        $this->cleanDir();
    }
    
    protected function tearDown() :void
    {
        parent::tearDown();
        $this->cleanDir();
    }
    
    /** @test */
    public function functions_in_the_custom_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-functions.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpAsPhpArray($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'bar',
            'foo',
            'readonly',
        ], $functions);
    }
    
    /** @test */
    public function classes_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-classes.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpAsPhpArray($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'AbstractTest',
            'WP_Error',
            'WP_User',
        ], $functions);
    }
    
    /** @test */
    public function interfaces_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-interfaces.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpAsPhpArray($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'FooInterface',
        ], $functions);
    }
    
    /** @test */
    public function traits_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-traits.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpAsPhpArray($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'BarTrait',
            'FooTrait',
        ], $functions);
    }
    
    /** @test */
    public function constants_in_the_global_namespace_are_parsed_correctly()
    {
        $expected_path = $this->dump_to.'/exclude-wordpress-constants.php';
        
        $this->assertFalse(is_file($expected_path));
        
        $this->dumper->dumpAsPhpArray($this->stub);
        
        $this->assertTrue(is_file($expected_path));
        
        $functions = require_once $expected_path;
        
        $this->assertSame([
            'DB_NAME',
            'DB_USER',
        ], $functions);
    }
    
    /**
     * @test
     */
    public function only_files_with_contents_can_be_dumped_as_php_array() :void
    {
        $expected_path = $this->dump_to.'/exclude-classes-only-classes.php';
    
        $this->assertFalse(is_file($expected_path));
    
        $this->dumper->dumpAsPhpArray($this->fixtures_dir.'/classes-only.php', false);
    
        $this->assertTrue(is_file($expected_path));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-traits.php'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-constants.php'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-functions.php'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-interfaces.php'));
    
        $functions = require_once $expected_path;
    
        $this->assertSame([
            'Bar',
            'Foo',
        ], $functions);
        
    }
    
      /**
     * @test
     */
    public function only_files_with_contents_can_be_dumped_as_json_array() :void
    {
        $expected_path = $this->dump_to.'/exclude-classes-only-classes.json';
    
        $this->assertFalse(is_file($expected_path));
    
        $this->dumper->dumpAsJson($this->fixtures_dir.'/classes-only.php', false);
    
        $this->assertTrue(is_file($expected_path));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-traits.json'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-constants.json'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-functions.json'));
        $this->assertFalse(is_file($this->dump_to.'/exclude-classes-only-interfaces.json'));
    
        $functions = json_decode(file_get_contents($expected_path));
    
        $this->assertSame([
            'Bar',
            'Foo',
        ], $functions);
        
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