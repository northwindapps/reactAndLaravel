<?php

function isValidVariable($var) {
    return preg_match('/^\$[a-zA-Z_][a-zA-Z0-9_]*(\[[0-9]+\])?$/', $var);
}

function isSingleVariable($var) {
    return preg_match('/^\$[a-zA-Z_][a-zA-Z0-9_]*$/', $var);
}

// Read variables from stdin
while (($line = fgets(STDIN)) !== false) {
    $var = trim($line);

    // Skip garbage lines
    if (!isValidVariable($var)) {
        fwrite(STDERR, "Error: Invalid variable detected -> $var\n");
        exit(1); // Stop process with a non-zero exit code
    }

    if (isSingleVariable($var)) {
        echo "$var: Single Variable\n";
    } else {
        echo "$var: Array Access\n";
    }
}
