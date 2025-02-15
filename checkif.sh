#!/bin/bash

# Set the target directory (default to current directory if not specified)
TARGET_DIR="${1:-.}"

# Output files to store results
OUTPUT_FILE="if_statements.txt"
OUTPUT_CONTENT_FILE="if_contents.txt"

# Clear output files if they already exist
> "$OUTPUT_FILE"
> "$OUTPUT_CONTENT_FILE"

# Find all files recursively and check for 'if' statements
find "$TARGET_DIR" -type f | while read -r file; do
    # Search for 'if' statements in the file
    if grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" &>/dev/null; then
        # Save the full 'if' statements
        grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" >> "$OUTPUT_FILE"
        # Extract and save only the conditions inside the if statements
        grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//;s/[[:space:]]//g' | sed -E 's/^.{3}//;s/.{2}$//'   >> "$OUTPUT_CONTENT_FILE"
    fi
done

echo "Done! Check '$OUTPUT_FILE' for full if-statements and '$OUTPUT_CONTENT_FILE' for extracted conditions."
