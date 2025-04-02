<?php
// Define the file path
$filePath = "./if_contents.txt"; // Change this to your actual file

// Check if the file exists
if (!file_exists($filePath)) {
    die("File not found!");
}

// Read the file
$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);


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


// Process each line
foreach ($lines as $line) {

    parsePHP($line);


    // global $test;
    // echo "start new line..\n\n";
    // $isValid = isParsable($line) ? "Valid ✅" : "Invalid ❌";
    // echo "Checking: $line -> $isValid\n";
    
    // // Output the collected test data for this line
    // var_dump($test);
    // $test = [];
}
?>
