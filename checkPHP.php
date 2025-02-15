<?php
// Define the file path
$filePath = "./if_contents.txt"; // Change this to your actual file
$test = array();
$localDims = array();

// Check if the file exists
if (!file_exists($filePath)) {
    die("File not found!");
}

// Read the file
$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Load AST extension if available
if (!extension_loaded('ast')) {
    die("AST extension is not installed.");
}

// Function to check if a line is parsable
function isParsable($code) {
    try {
        // Wrap in PHP tags to make it a valid PHP snippet
        $wrappedCode = "<?php " . $code . ";";
        $ast = \ast\parse_code($wrappedCode, 80);
        $modifiedAst = traverseAndApplyRules($ast);
        // Print the modified AST (optional)
        switchTreeStructure($modifiedAst);

        return true; // No syntax errors
    } catch (ParseError $e) {
        return false; // Syntax error detected
    }
}

function switchTreeStructure($node) {
    var_dump($node);
    if (isArrayAccess($node)) {
        echo "isArray\n";
        // Modify for array access
        return applyIssetRule($node);  // Wrap in isset check
    } else if (isVariable($node)) {
        echo "isVariable\n";
    }
    return $node;
}

function traverseAndApplyRules($node) {
    global $test;
    global $localDims;
    // Check if the node is a variable
    if (isVariable($node)) {
        if (isset($node->children['dim']) && !is_object($node->children['dim'])){
            // echo "Dim found: " . (String)$node->children['dim'] . "\n";
            array_push($localDims, (String)$node->children['dim']);
        }

        if (isset($node->children['name'])){
            echo "Variable found: " . $node->children['name'] . "\n";
            array_push($test, $node->children['name']);
            $test = array_merge($test, array_reverse($localDims));
            $localDims = array();
            return $node;
        }
    }

    // Apply rules to the current node
    $node = applyRule($node);

    // Recursively apply rules to children
    foreach ($node->children as $key => $child) {
        if ($child instanceof ast\Node) {
            $node->children[$key] = traverseAndApplyRules($child);
        }
    }

    return $node;
}

function applyRule($node) {
    global $test;
    if (isArrayAccess($node)) {
        if(isset($node->children[0]->children['expr']->children['name'])){
            array_push($test, $node->children[0]->children['expr']->children['name']);
        }
       
        if(!is_object($node->children[0]->children['dim'])){
            array_push($test, (string) $node->children[0]->children['dim']);
        }
       
        if(isset($node->children[0]->children['dim']->children['name'])){
            array_push($test, $node->children[0]->children['dim']->children['name']);
        }
        
        return ;
    }

    return $node;
}

function isArrayAccess($node) {
    // Checks if it's an array access (like $user[1])
    return isset($node->children[0]->children['dim']);
}

function isVariable($node) {
    // Check if it's a variable and if the name matches (e.g., $user)
    return !isset($node->children[0]->children['dim']);
}

// Process each line
foreach ($lines as $line) {
    global $test;
    echo "start new line..\n\n";
    $isValid = isParsable($line) ? "Valid ✅" : "Invalid ❌";
    echo "Checking: $line -> $isValid\n";
    
    // Output the collected test data for this line
    var_dump($test);
    $test = [];
}
?>
