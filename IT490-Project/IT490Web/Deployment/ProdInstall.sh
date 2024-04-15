#!/bin/bash

# Initialize variables
version=1
user='IT490DB'
password='IT490DB'
database='Deployment'
DeployIP="10.147.17.54" # Deployment machine IP
Pass="YogiMaster123@"
machine=""
bundleType=""
yourServiceName="apache2"

# Validate necessary commands
commands=("sshpass" "scp" "unzip" "mysql" "systemctl")
for cmd in "${commands[@]}"; do
    if ! command -v "$cmd" &> /dev/null; then
        echo "Error: Command $cmd is not available. Please install it."
        exit 1
    fi
done

# Prompt user for input
echo "Please enter the Prod (layer 3) machine (FE, BE, DMZ): "
read -r machine
echo "Please Enter the bundle type (FE,BE,DMZ,DB,CSS): "
read -r bundleType

# Ensure script is run with sudo if needed
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root or with sudo" 
   exit 1
fi

# Set configuration based on machine type
case $machine in
    "FE"|"BE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example" #Install Path
        yourServiceName="apache2" # Example service, adjust as necessary
        ;;
    "DMZ")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        installpath="/var/www/html/IT490-Project/IT490Web/DMZ" #Install Path
        yourServiceName="nginx" # Example service, adjust as necessary
        ;;
    *)
        echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
        exit 1
        ;;
esac

echo "Read $machine machine Location details"

# Function to determine the latest version bundle
get_latest_bundle() {
    local passedVersion
    passedVersion=$(mysql --host="$DeployIP" --user="$user" --password="$password" --database="$database" -sse "SELECT pkgName FROM versionHistory WHERE pkgName LIKE '${bundleType}v%' AND passed=1 ORDER BY version DESC LIMIT 1")
    echo "$passedVersion"
}

latestBundle=$(get_latest_bundle)
if [[ -n "$latestBundle" ]]; then
    echo "Latest bundle found: $latestBundle"
    echo "Copying $latestBundle to Prod machine..."
    if sshpass -v -p "$Pass" scp -o StrictHostKeyChecking=no "juanguti@$DeployIP:$path/$latestBundle/$bundleType.zip" "$installpath/"; then
        echo "Successfully copied $latestBundle."
        
        echo "Unzipping $latestBundle on Prod machine..."
        if unzip -o "$installpath/$bundleType.zip" -d "$installpath" && rm "$installpath/$bundleType.zip"; then
            echo "Successfully unzipped $latestBundle."
            
            echo "Restarting $yourServiceName service on Prod machine..."
            if sudo systemctl restart "$yourServiceName"; then
                echo "Prod installation and service restart completed successfully."
            else
                echo "Error restarting service $yourServiceName."
                exit 1
            fi
        else
            echo "Error unzipping $latestBundle. Check the integrity of the zip file."
            exit 1
        fi
    else
        echo "Error copying $latestBundle. Check if the file exists and is accessible."
        exit 1
    fi
else
    echo "No suitable bundle found for $bundleType. Exiting."
    exit 1
fi
