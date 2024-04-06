#!/bin/bash

# Initialize version variable
version=1

# MySQL login and DB configurations
user='IT490DB'
password='IT490DB'
database='Deployment'

# Prompt user for input
echo "Please enter the Production (layer 3) machine (FE, BE, DMZ): "
read machine
echo "Please Enter the config File Name(FE,BE,DMZ,DB,CSS .config): "
read configFile

# Set configuration based on machine type
case $machine in
    "FE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment" #config files
        #Change path here depending on files location on QA machine (FE,DMZ,BE)
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"

        prodMachineName="juanguti"
        PRODIP="10.147.17.206"
        prodPass="YogiMaster123@"
        echo "Read FE machine details"
        ;;
    "BE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment"
                #Change path here depending on files location on QA machine (FE,DMZ,BE)
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
        prodMachineName="ANGELTI490DEVUSERMACHINE"
        PRODIP="10.147.17.90"
        prodPass="ANGELIT490DEVPASSWORD"
        PRODIP="****DEVLAYERIPHERE****"
        echo "Read BE machine details"
        ;;
    "DMZ")
        path="/var/www/html/IT490-Project/IT490Web/DMZ"
                #Change path here depending on files location on QA machine (FE,DMZ,BE)
                #Change path here depending on files location on QA machine (FE,DMZ,BE)
        installpath="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
        prodMachineName="AngelDMZ490"
        PRODIP="CHANGE HERE"
        prodPass="dmz490"
        echo "Read DMZ machine details"
        ;;
    *)
        echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
        exit 1
        ;;
esac

# Function to determine the latest version bundle of the chosen type that has passed or is null
get_latest_bundle() {
    local passedVersion=$(mysql --user="$user" --password="$password" --database="$database" -sse "SELECT pkgName FROM versionHistory WHERE pkgName LIKE '${bundleType}v%' AND passed =1 ORDER BY version DESC LIMIT 1")
    echo "$passedVersion"
}

# Securely copy the latest bundle to the QA machine
latestBundle=$(get_latest_bundle)
if [[ -n "$latestBundle" ]]; then
    echo "Latest bundle found: $latestBundle"
    echo "Copying $latestBundle to Production machine..."
    scp -o StrictHostKeyChecking=no "/var/www/html/IT490-Project/IT490Web/Deployment/$latestBundle.zip" "$prodMachineName@$PRODIP:$installpath"
    
    echo "Unzipping $latestBundle on Production machine..."
    ssh "$prodMachineName@$PRODIP" "unzip -o $installpath/$latestBundle.zip -d $installpath && rm $installpath/$latestBundle.zip"
    
    # Assuming you have a way to identify the services to restart from the bundle name
    # Replace 'yourServiceName' with actual service name or extraction logic

    yourServiceName="apache2" # Modify this with actual logic or service name
    echo "Restarting services on Production machine..."
    ssh "$prodMachineName@$PRODIP" "sudo systemctl restart $yourServiceName"
    
    echo "Production installation and service restart completed."
else
    echo "No suitable bundle found for $bundleType. Exiting."
    exit 1
fi
