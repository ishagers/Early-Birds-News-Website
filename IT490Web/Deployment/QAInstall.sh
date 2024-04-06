#!/bin/bash

# Configuration for Deployment machine
DEPLOYMENT_USER="deploymentUser"
DEPLOYMENT_IP="deployment.machine.ip"
DEPLOYMENT_BASE_PATH="/var/www/html/IT490-Project/IT490Web/Deployment"
SSH_PASS="deploymentMachinePassword"

# Configuration for this machine (QA/Production)
INSTALL_PATH_FE="/var/www/html/FE" # Change this to the FE install path on this machine
INSTALL_PATH_BE="/var/www/html/BE" # Change this to the BE install path on this machine
INSTALL_PATH_DMZ="/var/www/html/DMZ" # Change this to the DMZ install path on this machine

# Determine the type of machine for deployment and set corresponding installation path
echo "Enter the type of machine for deployment (FE, BE, DMZ):"
read MACHINE_TYPE

case $MACHINE_TYPE in
    FE)
        INSTALL_PATH="$INSTALL_PATH_FE"
        ;;
    BE)
        INSTALL_PATH="$INSTALL_PATH_BE"
        ;;
    DMZ)
        INSTALL_PATH="$INSTALL_PATH_DMZ"
        ;;
    *)
        echo "Invalid machine type specified."
        exit 1
        ;;
esac

# Find the most recent or most recent passed version for the specific machine type
BUNDLE_PATH="$(sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no "$DEPLOYMENT_USER@$DEPLOYMENT_IP" "find $DEPLOYMENT_BASE_PATH -name '${MACHINE_TYPE}v*.zip' -print0 | xargs -r -0 ls -t | head -n1")"

# If no bundle found, exit
if [ -z "$BUNDLE_PATH" ]; then
    echo "No bundle found for $MACHINE_TYPE."
    exit 1
fi

# Extract the bundle name
BUNDLE_NAME=$(basename "$BUNDLE_PATH")

# Securely copy the bundle to the local machine
sshpass -p "$SSH_PASS" scp -o StrictHostKeyChecking=no "$DEPLOYMENT_USER@$DEPLOYMENT_IP:$BUNDLE_PATH" "$INSTALL_PATH/$BUNDLE_NAME"

# Unzip the bundle to the install path
unzip -o "$INSTALL_PATH/$BUNDLE_NAME" -d "$INSTALL_PATH" && rm "$INSTALL_PATH/$BUNDLE_NAME"

# Restart services if necessary (not included in pseudocode)

# Remove this script's own version if the installation is successful
# rm -- "$0"
