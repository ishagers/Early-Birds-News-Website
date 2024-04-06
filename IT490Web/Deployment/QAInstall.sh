#!/bin/bash

# Initialize variables
version=1
user='IT490DB'
password='IT490DB'
database='Deployment'
DeployIP="10.147.17.54" # Deployment machine IP
Pass = "YogiMaster123@"
# Prompt user for input
echo "Please enter the QA (layer 2) machine (FE, BE, DMZ): "
read machine
echo "Please Enter the bundle type (FE,BE,DMZ,DB,CSS): "
read bundleType

# Set configuration based on machine type
case $machine in
    "FE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example" #Install Path
        ;;
    "BE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example" #Install Path
        ;;
    "DMZ")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        installpath="/var/www/html/IT490-Project/IT490Web/DMZ" #Install Path
        ;;
    *)
        echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
        exit 1
        ;;
esac

echo "Read $machine machine Location details"

# Function to determine the latest version bundle that has passed or is null
get_latest_bundle() {
    local passedVersion=$(mysql --host="$DeployIP" --user="$user" --password="$password" --database="$database" -sse "SELECT pkgName FROM versionHistory WHERE pkgName LIKE '${bundleType}v%' AND (passed IS NULL OR passed=1) ORDER BY version DESC LIMIT 1")
    echo "$passedVersion"
}

latestBundle=$(get_latest_bundle)
if [[ -n "$latestBundle" ]]; then
    echo "Latest bundle found: $latestBundle"
    echo "Copying $latestBundle to QA machine..."
    sshpass -v -p "$Pass" scp -o StrictHostKeyChecking=no "$path/$latestBundle/$bundleType.zip" "juanguti@$DeployIP:$installpath"
    
    echo "Unzipping $latestBundle on QA machine..."
    sshpass -p "$Pass" ssh "juanguti@$DeployIP" "unzip -o $installpath/$bundleType.zip -d $installpath && rm $installpath/$bundleType.zip"
    
    # Modify this with actual logic or service name
    yourServiceName="apache2" # Modify based on actual service needed
    echo "Restarting services on QA machine..."
    sshpass -p "$Pass" ssh "juanguti@$DeployIP" "sudo systemctl restart $yourServiceName"
    
    echo "QA installation and service restart completed."
else
    echo "No suitable bundle found for $bundleType. Exiting."
    exit 1
fi
