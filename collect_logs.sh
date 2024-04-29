#!/bin/bash

# List of VM IP addresses
VMs=("10.147.17.233" "10.147.17.90")

# Path to the log file relative to the script's current directory
LOG_FILE="mylogfile.log"

# Command to generate a log entry
LOG_TRIGGER="echo 'Log event triggered at $(date)' >> $LOG_FILE"

# Execute the command locally to generate a log entry
echo "Generating a log event locally..."
$LOG_TRIGGER

# Monitor the log file locally in real time
echo "Monitoring local log file..."
tail -f $LOG_FILE &
LOCAL_TAIL_PID=$!
sleep 5  # Allow some time for monitoring before killing the tail process
kill $LOCAL_TAIL_PID

# Loop through each VM IP address
for vm in "${VMs[@]}"
do
    echo "Generating a log event on $vm..."
    # Assumes the same directory structure exists on the remote VMs
    ssh user@$vm "cd ~/Documents/GitHub/IT490-Project && $LOG_TRIGGER"

    echo "Monitoring log file on $vm..."
    ssh -t user@$vm "cd ~/Documents/GitHub/IT490-Project && tail -f $LOG_FILE" &
    REMOTE_TAIL_PID=$!
    sleep 5  # Allow some time for monitoring before killing the tail process
    ssh user@$vm "kill $REMOTE_TAIL_PID"
done

echo "Log collection and monitoring complete."

