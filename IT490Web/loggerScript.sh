#!/bin/bash

# Directory where the script is located
SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

# Path to the log file
LOG_FILE="$SCRIPT_DIR/my_script.log"

# Timestamp
TIMESTAMP=$(date +"%Y-%m-%d %T")

# Message to log
MESSAGE="Hello from my script!"

# Write the message to the log file
echo "$TIMESTAMP - $MESSAGE" >> "$LOG_FILE"

