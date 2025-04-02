<?php
$file_path = $_SERVER['argv'][1];
$file = fopen($file_path, 'r');
if ($file) {
    // Read the file contents
    $contents = fread($file, filesize($file_path));
    echo $contents;
    fclose($file);
} else {
    echo "Error opening file: $file_path";
}
?>