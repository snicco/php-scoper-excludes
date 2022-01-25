<?php

declare(strict_types=1);

namespace Snicco\PHPScoperWPExludes\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class Filter extends NodeVisitorAbstract
{
    
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            return null;
        }
        
        // We don't need to traverse child nodes like methods on classes since we
        // are only interested in the root names.
        // This way we improve performance by a lot.
        if ($this->isOfInterest($node)) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }
    
    private function isOfInterest(Node $node) :bool
    {
        return $node instanceof Node\Stmt\Class_
               || $node instanceof Node\Stmt\Function_
               || $node instanceof Node\Stmt\Trait_
               || $node instanceof Node\Stmt\Const_
               || $node instanceof Node\Stmt\Interface_
               || ($node instanceof Node\Stmt\Expression
                   && $node->expr instanceof Node\Expr\FuncCall
                   && $node->expr->name instanceof Node\Name
                   && $node->expr->name->toString() === 'define'
               );
    }
    
}