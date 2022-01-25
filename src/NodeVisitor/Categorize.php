<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes\NodeVisitor;

use Throwable;
use PhpParser\Node;
use RuntimeException;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class Categorize extends NodeVisitorAbstract
{
    
    /**
     * @var string[]
     */
    private array $classes = [];
    
    /**
     * @var string[]
     */
    private array $interfaces = [];
    
    /**
     * @var string[]
     */
    private array $functions = [];
    
    /**
     * @var string[]
     */
    private array $traits = [];
    
    /**
     * @var string[]
     */
    private array $constants = [];
    
    public function beforeTraverse(array $nodes)
    {
        $this->reset();
        return null;
    }
    
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->addClassNames($node);
            return;
        }
        if ($node instanceof Node\Stmt\Interface_) {
            $this->addInterfaceNames($node);
            return;
        }
        if ($node instanceof Node\Stmt\Function_) {
            $this->addFunctionNames($node);
            return;
        }
        if ($node instanceof Node\Stmt\Trait_) {
            $this->addTraitNames($node);
            return;
        }
        if ($node instanceof Node\Stmt\Const_) {
            $this->addConstantNames($node);
            return;
        }
        
        if ($node instanceof Node\Stmt\Expression) {
            $this->addDefineConstantNames($node);
        }
        
        return null;
    }
    
    public function classes() :array
    {
        return $this->classes;
    }
    
    public function functions() :array
    {
        return $this->functions;
    }
    
    public function traits() :array
    {
        return $this->traits;
    }
    
    public function constants() :array
    {
        return $this->constants;
    }
    
    public function interfaces() :array
    {
        return $this->interfaces;
    }
    
    private function reset()
    {
        $this->classes = [];
        $this->functions = [];
        $this->traits = [];
        $this->constants = [];
    }
    
    private function addClassNames(Node $node) :void
    {
        if ( ! isset($node->namespacedName)) {
            throw new RuntimeException(
                "Class node was expected to be a namespacedName attribute."
            );
        }
        $this->classes[] = $node->namespacedName->toString();
    }
    
    private function addFunctionNames(Node $node) :void
    {
        if ( ! isset($node->namespacedName)) {
            throw new RuntimeException(
                'Function node was expected to have a namespacedName attribute.'
            );
        }
        $this->functions[] = $node->namespacedName->toString();
    }
    
    private function addTraitNames(Node $node) :void
    {
        if ( ! isset($node->namespacedName)) {
            throw new RuntimeException(
                'Trait node was expected to have a namespacedName attribute.'
            );
        }
        $this->traits[] = $node->namespacedName->toString();
    }
    
    private function addConstantNames(Node $node) :void
    {
        if (empty($node->consts)) {
            throw new RuntimeException("Constant declaration node has no constants.");
        }
        
        foreach ($node->consts as $const) {
            if ( ! isset($const->namespacedName)) {
                throw new RuntimeException(
                    'Const node was expected to have a namespacedName attribute.'
                );
            }
            
            $this->constants[] = $const->namespacedName->toString();
        }
    }
    
    private function addDefineConstantNames(Node $node) :void
    {
        if ( ! isset($node->expr->args)) {
            throw new RuntimeException("define() declaration has no constant name.");
        }
        
        try {
            $this->constants[] = (string) $node->expr->args[0]->value->value;
        } catch (Throwable $e) {
            throw new RuntimeException(
                "define() declaration has no constant name.\n{$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }
    
    private function addInterfaceNames(Node $node)
    {
        if ( ! isset($node->namespacedName)) {
            throw new RuntimeException(
                'Interface node was expected to be a namespacedName attribute.'
            );
        }
        $this->interfaces[] = $node->namespacedName->toString();
    }
    
}