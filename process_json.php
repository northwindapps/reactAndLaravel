<?php
if ($argc < 2) {
    die("Usage: php process_json.php <json_file>\n");
}

$jsonFile = $argv[1];

if (!file_exists($jsonFile)) {
    die("Error: File $jsonFile not found!\n");
}

// Read and decode JSON
$jsonData = json_decode(file_get_contents($jsonFile), true);

if (!$jsonData) {
    die("Error: Invalid JSON format!\n");
}

// Process each entry
foreach ($jsonData as $entry) {
    echo "File: " . $entry['file'] . "\n";
    echo "Line: " . $entry['line'] . "\n";
    echo "Condition: " . $entry['condition'] . "\n";
    echo "Array Variables: " . $entry['array_variables'] . "\n";
    echo "----------------------------------\n";

    $path = $entry['file'];
    $lineNumber = (int)$entry['line'];

    if (!file_exists($path)) {
        echo "Error: File not found - $path\n";
        continue;
    }

    // Convert JSON string to array
    $condition = $entry['condition'];
    if (!is_string($condition)) {
        $condition = '';
    }
    echo $condition;
    if (!empty($condition) && $lineNumber > 0) {
        // Read file into an array (each line is an element)
        $fileContents = file($path);

        if ($lineNumber <= count($fileContents)) {
            $originalLine = $fileContents[$lineNumber - 1];
            $replacement = 'isset(' . $condition . ') && ' . $condition;
            $med_line = str_replace($condition, $replacement, $originalLine);
            $modifiedLine = preg_replace('/\b' . preg_quote($condition, '/') . '\b/', $replacement, $originalLine);
            // echo $modifiedLine;
            $fileContents[$lineNumber - 1] = $med_line;
            // Write modified contents back to the file
            file_put_contents($path, implode("", $fileContents));
            echo "Updated line $lineNumber in: $path\n";
        } else {
            echo "Error: Line number $lineNumber exceeds file length in $path\n";
        }
    }
}
?>
