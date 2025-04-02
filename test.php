<?php
$file = 'test.txt';
$target = '$user[1]';
$replacement = 'isset($user[1]) && $user[1]';

$file_contents = file_get_contents($file);
$file_contents = str_replace($target, $replacement, $file_contents);
file_put_contents($file, $file_contents);
?>