#!/bin/bash

# Read the filename from the argument
input_file="$1"

# The string you want to replace
target='\$user$$1$$'

# Replacement string
replacement='isset(\$user$$1$$) \& \$user$$1$$'

# Process each line of the file
sed -i.bak "s/$target/$replacement/g" "$input_file"