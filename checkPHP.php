<?php
// Define the file path
$filePath = "./if_contents.txt"; // Change this to your actual file

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
        \ast\parse_code($wrappedCode, 80);
        return true; // No syntax errors
    } catch (ParseError $e) {
        return false; // Syntax error detected
    }
}

// Process each line
foreach ($lines as $line) {
    $isValid = isParsable($line) ? "Valid ✅" : "Invalid ❌";
    echo "Checking: $line -> $isValid\n";
}
?>
