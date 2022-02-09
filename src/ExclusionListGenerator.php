<?php

declare(strict_types=1);

namespace Snicco\PhpScoperExcludes;

use Closure;
use RuntimeException;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use InvalidArgumentException;
use PhpParser\NodeVisitor\NameResolver;
use Snicco\PhpScoperExcludes\NodeVisitor\Filter;
use Snicco\PhpScoperExcludes\NodeVisitor\Categorize;

use function count;
use function is_dir;
use function pathinfo;
use function var_export;
use function str_replace;
use function is_writable;
use function is_readable;
use function json_encode;
use function array_filter;
use function file_get_contents;
use function file_put_contents;

use const JSON_PRETTY_PRINT;
use const PATHINFO_FILENAME;
use const PATHINFO_EXTENSION;
use const JSON_THROW_ON_ERROR;

/**
 * @api
 */
final class ExclusionListGenerator
{
    
    const STMT_FUNCTION = 'function';
    const STMT_CLASS = 'class';
    const STMT_CONST = 'const';
    const STMT_TRAIT = 'trait';
    const STMT_INTERFACE = 'interface';
    
    private Parser $parser;
    private string $root_dir;
    
    public function __construct(Parser $parser, string $root_dir)
    {
        if ( ! is_dir($root_dir)) {
            throw new InvalidArgumentException("Directory [$root_dir] does not exist.");
        }
        
        if ( ! is_writable($root_dir)) {
            throw new InvalidArgumentException("Directory [$root_dir] is not writable.");
        }
        
        $this->parser = $parser;
        $this->root_dir = $root_dir;
    }
    
    public function dumpAsPhpArray(string $file, bool $include_empty = true) :void
    {
        $this->dump($file, function (array $exludes, string $file_path) {
            return file_put_contents(
                $file_path,
                '<?php return '.var_export($exludes, true).';'
            );
        }, '.php', $include_empty);
    }
    
    public function dumpAsJson(string $file, bool $include_empty = true) :void
    {
        $this->dump($file, function (array $excludes, $file_path) {
            $json = json_encode($excludes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            return file_put_contents($file_path, $json);
        }, '.json', $include_empty);
    }
    
    /**
     * @param  Closure(array,string):bool  $save_do_disk
     */
    private function dump(string $file, Closure $save_do_disk, string $file_extension, bool $include_empty) :void
    {
        if ( ! is_readable($file)) {
            throw new InvalidArgumentException("File [$file] is not readable.");
        }
        
        if ('php' !== pathinfo($file, PATHINFO_EXTENSION)) {
            throw new InvalidArgumentException(
                "Only PHP files can be processed.\nCant process file [$file]."
            );
        }
        
        $content = file_get_contents($file);
        if (false === $content) {
            throw new RuntimeException("Cant read file contents of file [$file].");
        }
        
        $exclude_list = $this->generateExcludeList($content);
        
        $base_name = pathinfo($file, PATHINFO_FILENAME);
        
        if(!$include_empty){
            $exclude_list = array_filter($exclude_list, fn(array $arr) => count($arr));
        }
        
        foreach ($exclude_list as $type => $excludes) {
            
            $path = $this->getFileName($type, $base_name, $file_extension);
            $success = $save_do_disk($excludes, $path);
            
            if (false === $success) {
                throw new RuntimeException("Could not dump contents for file [$base_name].");
            }
        }
    }
    
    /**
     * @return array<string,string[]>
     */
    private function generateExcludeList(string $file_contents) :array
    {
        $node_traverser = new NodeTraverser();
        $node_traverser->addVisitor(new Filter());
        $node_traverser->addVisitor(new NameResolver());
        // The order is important.
        $node_traverser->addVisitor($categorizing_visitor = new Categorize());
        
        $ast = $this->parser->parse($file_contents);
        $node_traverser->traverse($ast);
        
        return [
            self::STMT_CLASS => $categorizing_visitor->classes(),
            self::STMT_INTERFACE => $categorizing_visitor->interfaces(),
            self::STMT_FUNCTION => $categorizing_visitor->functions(),
            self::STMT_TRAIT => $categorizing_visitor->traits(),
            self::STMT_CONST => $categorizing_visitor->constants(),
        ];
    }
    
    private function getFileName(string $key, string $file_basename, string $extension) :string
    {
        $file_basename = str_replace('-stubs', '', $file_basename);
        $file_basename = str_replace($extension, '', $file_basename);
        switch ($key) {
            case self::STMT_FUNCTION:
                return $this->root_dir."/exclude-$file_basename-functions$extension";
            case self::STMT_CLASS:
                return $this->root_dir."/exclude-$file_basename-classes$extension";
            case self::STMT_INTERFACE:
                return $this->root_dir."/exclude-$file_basename-interfaces$extension";
            case self::STMT_CONST:
                return $this->root_dir."/exclude-$file_basename-constants$extension";
            case self::STMT_TRAIT:
                return $this->root_dir."/exclude-$file_basename-traits$extension";
            default:
                throw new RuntimeException("Unknown exclude identifier [$key].");
        }
    }
    
}