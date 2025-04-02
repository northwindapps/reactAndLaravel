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
            $newConditions = [];

            // Traverse the if condition expression
            $this->extractConditions($node->cond, $newConditions);

            // Wrap conditions with isset() checks
            $issetChecks = [];
            foreach ($newConditions as $condition) {
                if ($condition instanceof Node\Expr\ArrayDimFetch) {
                    $issetChecks[] = new Node\Expr\Isset_([$condition]);
                } elseif ($condition instanceof Node\Expr\Variable) {
                    $issetChecks[] = new Node\Expr\Isset_([$condition]);
                }
            }

            // Combine the isset checks with the original condition
            if (!empty($issetChecks)) {
                $node->cond = $this->combineConditionsWithIsset($issetChecks, $node->cond);
            }
        }

        return $node;
    }

    private function extractConditions($expr, &$conditions) {
        if ($expr instanceof Node\Expr\BinaryOp) {
            $this->extractConditions($expr->left, $conditions);
            $this->extractConditions($expr->right, $conditions);
        } else {
            $conditions[] = $expr;
        }
    }

    private function combineConditionsWithIsset($issetChecks, $originalCondition) {
        // If there are multiple isset checks, combine them using AND
        if (count($issetChecks) > 1) {
            $combinedIsset = new Node\Expr\BinaryOp\BooleanAnd(
                array_shift($issetChecks),
                array_shift($issetChecks)
            );

            foreach ($issetChecks as $issetCheck) {
                $combinedIsset = new Node\Expr\BinaryOp\BooleanAnd($combinedIsset, $issetCheck);
            }

            // Combine the combined isset with the original condition
            return new Node\Expr\BinaryOp\BooleanAnd($combinedIsset, $originalCondition);
        } elseif (count($issetChecks) === 1) {
            return new Node\Expr\BinaryOp\BooleanAnd($issetChecks[0], $originalCondition);
        }

        return $originalCondition;
    }
}

// 1️⃣ Parse PHP code
$parser = (new ParserFactory())->createForNewestSupportedVersion();
$code = '<?php if ($user[1] && $reason[0] || $other[2] && $other[$i] || $header->arg[0]) { echo "Valid!"; }';
$ast = $parser->parse($code);

// 2️⃣ Traverse & Modify the AST
$traverser = new NodeTraverser();
$traverser->addVisitor(new AddIssetChecksVisitor());
$modifiedAst = $traverser->traverse($ast);

// 3️⃣ Generate New PHP Code
$prettyPrinter = new PrettyPrinter\Standard();
$newCode = $prettyPrinter->prettyPrintFile($modifiedAst);

echo $newCode;
