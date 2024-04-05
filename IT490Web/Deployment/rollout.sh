#!/bin/bash

# Initialize version variable
version=1

# MySQL login and DB configurations
user='IT490DB'
password='IT490DB'
database='Deployment'

# Prompt user for input
echo "Please enter the dev machine (FE, BE, DMZ): "
read machine
echo "Please Enter the config File Name(FE,BE,DMZ,DB,CSS .config): "
read configFile

# Set configuration based on machine type
case $machine in
    "FE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment"
        devMachineName="juanguti"
        devIP="10.147.17.233"
        devPass="YogiMaster123@"
        echo "Read FE machine details"
        ;;
    "BE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment"
        devMachineName="ANGELTI490DEVUSERMACHINE"
        devIP="10.147.17.90"
        devPass="ANGELIT490DEVPASSWORD"
        echo "Read BE machine details"
        ;;
    "DMZ")
        path="/var/www/html/IT490-Project/IT490Web/DMZ"
        devMachineName="AngelDMZ490"
        devIP="10.147.17.227"
        devPass="dmz490"
        echo "Read DMZ machine details"
        ;;
    *)
        echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
        exit 1
        ;;
esac

# Function to increment version number and find a non-existing directory
increment_version() {
    while [ -d "v$version" ]; do
        let "version=version+1"
    done
}

# Call the increment_version function to find the correct version directory to create
increment_version

# Now that we have the correct version, create the directory and move into it
mkdir "v$version" && cd "v$version" || exit
echo "Creating and moving into directory v$version..."

# Securely copy config file into folder
sshpass -v -p "$devPass" scp -o StrictHostKeyChecking=no "$devMachineName@$devIP:$path/$configFile" "./$configFile"
echo "SCP command completed."

# Read config file into array
IFS=$'\n' read -d '' -r -a lines < "$configFile"

# Extract package info from config
pkgName=${lines[2]}
installLoc=${lines[5]}
qaMachine=${lines[4]}
services=${lines[7]}
length=${#lines[@]}

# Assume the actual files to be copied are located at the install location specified in the config file
path=$installLoc

# Copy files outlined in config
for ((i=9; i<${length}; i++)); do
    echo "Copying ${lines[i]} from dev..."
    sshpass -v -p "$devPass" scp "$devMachineName@$devIP:$path/${lines[i]}" "./${lines[i]}"
done

# Zip files excluding the config
zip -r -j "$pkgName.zip" ./* -x "*.config"

echo "Package $pkgName.zip created."

# Insert version info into database
mysql --user="$user" --password="$password" --database="$database" -e "INSERT INTO versionHistory (version, pkgName, passed) VALUES ('$version', '$pkgName', NULL);"
echo "Version: $version pushed with package name: $pkgName"

# Restart services based on the config
IFS=',' read -r -a servicesArray <<< "$services"
for service in "${servicesArray[@]}"; do
    echo "Attempting to restart $service..."
    # Use SSH to execute service restart on the target machine
    ssh -t "$devMachineName@$devIP" "echo $devPass | sudo -S systemctl restart $service"
done
