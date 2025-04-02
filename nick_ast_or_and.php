<?php

require 'vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class AddIssetChecksVisitor extends NodeVisitorAbstract {
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Stmt\If_) {
            $this->applyIssetToConditions($node->cond);
        }
        return $node;
    }

    private function applyIssetToConditions(&$condition) {
        if ($condition instanceof Node\Expr\BinaryOp) {
            // For BinaryOp, apply isset to both left and right operands
            if ($condition->left instanceof Node\Expr\Variable || $condition->left instanceof Node\Expr\ArrayAccess) {
                $condition->left = new Node\Expr\Isset_([$condition->left]);
            }
            if ($condition->right instanceof Node\Expr\Variable || $condition->right instanceof Node\Expr\ArrayAccess) {
                $condition->right = new Node\Expr\Isset_([$condition->right]);
            }

            // Recursively handle left and right operands if they are BinaryOps
            $this->applyIssetToConditions($condition->left);
            $this->applyIssetToConditions($condition->right);
        }
    }
}

// 1️⃣ Parse PHP code
$parser = (new ParserFactory())->createForNewestSupportedVersion();
$code = '<?php if ($user[1] && $reason[0] || $other[2]) { echo "Valid!"; }';
$ast = $parser->parse($code);

// 2️⃣ Traverse & Modify the AST
$traverser = new NodeTraverser();
$traverser->addVisitor(new AddIssetChecksVisitor());
$modifiedAst = $traverser->traverse($ast);

// 3️⃣ Generate New PHP Code
$prettyPrinter = new PrettyPrinter\Standard();
$newCode = $prettyPrinter->prettyPrintFile($modifiedAst);

echo $newCode;
