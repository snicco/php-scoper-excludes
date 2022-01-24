<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes;

use RuntimeException;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use InvalidArgumentException;

use function is_readable;
use function array_merge;
use function file_get_contents;

final class FileDumper
{
    
    private array $extract_statements = [
        'Stmt_Class',
        'Stmt_Interface',
        'Stmt_Trait',
        'Stmt_Function'
    ];
    
    /**
     * @var string[]
     */
    private array $files;
    
    public function __construct(array $files) {
        foreach ($files as $file) {
            if(!is_readable($file)){
                throw new InvalidArgumentException("file [$file] is not readable.");
            }
        }
        $this->files = $files;
    }
    
    public function dumpExludes(string $root_dir) :void
    {
        $identifiers = [];
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            
            if(false === $content){
                throw new RuntimeException("Cant read file contents of file [$file].");
            }
            
            $ast = $this->generateAst($content);
            $res = $this->extractIdentifiersFromAst($ast);
            $identifiers = array_merge($identifiers,$res);
        }
    
        $foo = 'bar';
    }
    
    private function generateAst(string $content) :array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        return $parser->parse($content);
    }
    
    /**
     * @param  Stmt[]  $ast
     *
     */
    protected function extractIdentifiersFromAst(array $ast) :array
    {
        $globals = [];
        $items = $ast;
        
        while (count($items) > 0) {
            
            $item = array_pop($items);
            
            if (isset($item->stmts)) {
                $items = array_merge($items, $item->stmts);
            }
            
            if (in_array($item->getType(), $this->extract_statements)) {
                $name = $item->name;
                $string = $name->toString();
                $globals[] = $item->name;
            }
        }
        
        return $globals;
    }
    
    
}