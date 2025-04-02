#!/bin/bash

# Set the target directory (default to current directory if not specified)
TARGET_DIR="${1:-.}"

# Output files to store results
OUTPUT_FILE="if_statements.txt"
OUTPUT_CONTENT_FILE="if_contents.txt"
OUTPUT_CONTENT_ARRAY_VAR_FILE="if_contents_arrayvar.txt"

# Clear output files if they already exist
> "$OUTPUT_FILE"
> "$OUTPUT_CONTENT_FILE"
> "$OUTPUT_CONTENT_ARRAY_VAR_FILE"

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

# Extract array-like variables from the conditions
grep -oE '\$[a-zA-Z_][a-zA-Z0-9_]*\[[0-9]+\]' "$OUTPUT_CONTENT_FILE" >> "$OUTPUT_CONTENT_ARRAY_VAR_FILE"

# Find all files recursively and check for 'if' statements
# find "$TARGET_DIR" -type f | while read -r file; do
#     # Search for 'if' statements in the file and print file path + line number
#     grep -Eni '^\s*if\s*\(?.*\)?\s*{' "$file" | while read -r line; do
#         line_number=$(echo "$line" | cut -d: -f1)
#         if_statement=$(echo "$line" | cut -d: -f2-)

#         # Save full 'if' statement with file path & line number
#         echo "$file:$line_number: $if_statement" >> "$OUTPUT_FILE"

#         # Extract and save only the conditions inside the 'if' statements
#         condition=$(echo "$if_statement" | sed -E 's/^[[:space:]]*if[[:space:]]*\(?(.*)\)?[[:space:]]*{/\1/')
#         echo "$file:$line_number: $condition" >> "$OUTPUT_CONTENT_FILE"
#     done
# done

# Run PHP script to classify variables
php -f classify_variables.php < "$OUTPUT_CONTENT_ARRAY_VAR_FILE"

echo "Done! Check '$OUTPUT_FILE' for full if-statements and '$OUTPUT_CONTENT_FILE' for extracted conditions."
