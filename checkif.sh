#!/bin/bash

# Set the target directory (default to current directory if not specified)
TARGET_DIR="${1:-.}"

# Output files to store results
OUTPUT_FILE="if_statements.txt"
OUTPUT_CONTENT_FILE="if_contents.txt"
OUTPUT_CONTENT_JSON_FILE="if_contents.json"

# Clear output files if they already exist
> "$OUTPUT_FILE"
> "$OUTPUT_CONTENT_FILE"
> "$OUTPUT_CONTENT_JSON_FILE"

# Find all files recursively and check for 'if' statements
find "$TARGET_DIR" -type f | while read -r file; do
    # Search for 'if' statements in the file
    if grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" &>/dev/null; then
        # Save the full 'if' statements
        grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" >> "$OUTPUT_FILE"
        # Extract and save only the conditions inside the if statements
        # grep -Ei '^\s*if\s*\(?.*\)?\s*{' "$file" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//;s/[[:space:]]//g' | sed -E 's/^.{3}//;s/.{2}$//'   >> "$OUTPUT_CONTENT_FILE"
        grep -Ei '^\s*if\s*\(?.*\)?' "$file" | sed -E 's/^[[:space:]]*if[[:space:]]*\((.*)\)[[:space:]]*/\1/' >> "$OUTPUT_CONTENT_FILE"

    fi
done

# Initialize the JSON array
echo "[" > $OUTPUT_CONTENT_JSON_FILE

# Find all files recursively and check for 'if' statements
find "$TARGET_DIR" -type f | while read -r file; do
    # Search for 'if' statements in the file and print file path + line number
    grep -Eni '^\s*if\s*\(?.*\)?\s*{' "$file" | while read -r line; do
        line_number=$(echo "$line" | cut -d: -f1)
        if_statement=$(echo "$line" | cut -d: -f2-)

        # Extract and save only the conditions inside the 'if' statements
        condition=$(echo "$if_statement" | sed -E 's/^[[:space:]]*if[[:space:]]*\(?(.*)\)?[[:space:]]*{/\1/')

        # Use grep to find array variables (e.g., $user[1])
        array_variables=$(echo "$condition" | grep -oE '\$[a-zA-Z_][a-zA-Z0-9_]*\[[0-9]+\]' | tr '\n' ' && ')
        array_variables=$(echo "$array_variables" | sed 's/[[:space:]]*$//')  # Trim spaces

        # Output the JSON object for each 'if' statement with extracted array variables
        echo "  {\"file\":\"$file\",\"condition\":\"$array_variables\",\"line\":\"$line_number\",\"array_variables\":\"$array_variables\"}," >> $OUTPUT_CONTENT_JSON_FILE
    done
done

# Close the JSON array (remove trailing comma and append closing bracket)
sed -i '' -e '$ s/,$//' $OUTPUT_CONTENT_JSON_FILE  # Remove trailing comma from the last JSON object
echo "]" >> $OUTPUT_CONTENT_JSON_FILE  # Close the JSON array

# Run the PHP script and pass the JSON file as an argument
php process_json.php "$OUTPUT_CONTENT_JSON_FILE"


# echo "Done! Check '$OUTPUT_FILE' for full if-statements and '$OUTPUT_CONTENT_FILE' for extracted conditions."
