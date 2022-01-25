<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes;

use PhpParser\Node;
use RuntimeException;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use InvalidArgumentException;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeVisitor\NameResolver;

use function is_dir;
use function in_array;
use function basename;
use function var_export;
use function is_readable;
use function array_merge;
use function str_replace;
use function file_get_contents;
use function file_put_contents;

final class FileDumper
{
    
    const STMT_FUNCTION = 'Stmt_Function';
    const STMT_CLASS = 'Stmt_Class';
    const STMT_CONST = 'Stmt_Const';
    const STMT_TRAIT = 'Stmt_Trait';
    const STMT_EXPRESSION = 'Stmt_Expression';
    const STMT_INTERFACE = 'Stmt_Interface';
    
    /**
     * @var string[]
     */
    private array $files;
    
    public function __construct(array $files)
    {
        foreach ($files as $file) {
            if ( ! is_readable($file)) {
                throw new InvalidArgumentException("file [$file] is not readable.");
            }
        }
        $this->files = $files;
    }
    
    public function dumpExludes(string $root_dir) :void
    {
        if ( ! is_dir($root_dir)) {
            throw new RuntimeException("$root_dir is not a directory.");
        }
        
        $identifiers = [];
        
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            
            if (false === $content) {
                throw new RuntimeException("Cant read file contents of file [$file].");
            }
            
            $ast = $this->generateAst($content);
            
            $name_resolver = new NameResolver();
            $node_traverser = new NodeTraverser();
            $node_traverser->addVisitor($name_resolver);
            
            $ast = $node_traverser->traverse($ast);
            
            $statements = $this->flattenAst($ast);
            
            foreach ($statements as $statement) {
                if ( ! $this->isOfInterest($statement)) {
                    continue;
                }
                
                if (isset($statement->namespacedName)) {
                    $type = $statement->getType();
                    $type = (self::STMT_INTERFACE === $type) ? self::STMT_CLASS : $type;
                    
                    $identifiers[basename($file)][$type][] =
                        (string) $statement->namespacedName;
                    continue;
                }
                if (isset($statement->consts)) {
                    foreach ($statement->consts as $const) {
                        $identifiers[basename($file)][$statement->getType()][] =
                            (string) $const->namespacedName;
                    }
                    continue;
                }
                if (isset($statement->expr)) {
                    $identifiers[basename($file)][self::STMT_CONST][] =
                        (string) $statement->expr->args[0]->value->value;
                }
            }
        }
        
        foreach ($identifiers as $base_name => $types) {
            foreach ($types as $type => $excludes) {
                $name = $this->getFileName($type, $base_name, $root_dir);
                
                $success = file_put_contents(
                    $name,
                    '<?php return '.var_export($excludes, true).';'
                );
                
                if (false === $success) {
                    throw new RuntimeException("Could not dump contents for file [$base_name].");
                }
            }
        }
    }
    
    private function generateAst(string $content) :array
    {
        $parser = (new ParserFactory)->create(
            ParserFactory::PREFER_PHP7,
            new Emulative(['phpVersion' => '8.0'])
        );
        return $parser->parse($content);
    }
    
    /**
     * @param  Node[]  $ast
     *
     * @return array
     */
    private function flattenAst(array $ast) :array
    {
        $res = [];
        
        foreach ($ast as $statement) {
            if (isset($statement->stmts)) {
                $res = array_merge($res, $statement->stmts);
            }
        }
        return $res;
    }
    
    private function isOfInterest(Node $statement) :bool
    {
        if (in_array($statement->getType(), [
            self::STMT_TRAIT,
            self::STMT_CLASS,
            self::STMT_FUNCTION,
            self::STMT_CONST,
            self::STMT_INTERFACE,
        ], true)) {
            return true;
        }
        
        if (self::STMT_EXPRESSION === $statement->getType()) {
            if ( ! isset($statement->expr)) {
                return false;
            }
            
            if ( ! isset($statement->expr->name)) {
                return false;
            }
            
            if ((string) $statement->expr->name === 'define') {
                return true;
            }
        }
        return false;
    }
    
    private function getFileName(string $key, string $file_basename, string $root_dir) :string
    {
        $file_basename = str_replace('-stubs.php', '', $file_basename);
        $file_basename = str_replace('.php', '', $file_basename);
        switch ($key) {
            case self::STMT_FUNCTION:
                return $root_dir."/exclude-$file_basename-functions.php";
            case self::STMT_CLASS:
            case self::STMT_INTERFACE:
                return $root_dir."/exclude-$file_basename-classes.php";
            case self::STMT_CONST:
                return $root_dir."/exclude-$file_basename-constants.php";
            case self::STMT_TRAIT:
                return $root_dir."/exclude-$file_basename-traits.php";
            default:
                throw new RuntimeException("Unknown key [$key].");
        }
    }
    
}