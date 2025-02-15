#!/bin/bash

# Set the target directory (default to current directory if not specified)
TARGET_DIR="${1:-.}"

# Find all files recursively and process them
find "$TARGET_DIR" -type f | while read -r file; do
    echo "Processing file: $file"
    # Add your processing logic here
done
