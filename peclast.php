<?php

function parsePHP($code) {
    $ast = ast\parse_code("<?php $code;", $version=70); // PHP 7+
    printAST($ast);
}

function printAST($node, $indent = 0) {
    if (!$node instanceof ast\Node) {
        echo str_repeat("  ", $indent) . gettype($node) . " - " . json_encode($node) . "\n";
        return;
    }

    echo str_repeat("  ", $indent) . ast\get_kind_name($node->kind) . "\n";

    foreach ($node->children as $key => $child) {
        echo str_repeat("  ", $indent + 1) . "$key:\n";
        printAST($child, $indent + 2);
    }
}

$code = 'isset($user[1]) && $user[1] && $user[2]';
parsePHP($code);
?>
