#!/bin/bash

# Initialize version variable
version=1

# MySQL login and DB configurations
user='IT490DB'
password='IT490DB'
database='Deployment'

# Prompt user for input
echo "Please enter the dev(layer 1) machine (FE, BE, DMZ): "
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
        qaIP="10.147.17.67"
        echo "Read FE machine details"
        ;;
    "BE")
        path="/var/www/html/IT490-Project/IT490Web/Deployment"
        devMachineName="ANGELTI490DEVUSERMACHINE"
        devIP="10.147.17.90"
        devPass="ANGELIT490DEVPASSWORD"
        qaIP="****DEVLAYERIPHERE****"
        echo "Read BE machine details"
        ;;
    "DMZ")
        path="/var/www/html/IT490-Project/IT490Web/DMZ"
        devMachineName="AngelDMZ490"
        devIP="10.147.17.227"
        devPass="dmz490"
        qaIP="****DEVLAYERIPHERE****"
        echo "Read DMZ machine details"
        ;;
    *)
        echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
        exit 1
        ;;
esac

type=${configFile%.config}
# Function to determine the latest version for a specific machine type
get_latest_version() {
    echo $(mysql --user="$user" --password="$password" --database="$database" -sse "SELECT MAX(version) FROM versionHistory WHERE pkgName LIKE '$type%'")
}

# Function to create a new version directory
create_version_directory() {
    new_version=$(($1 + 1))
    new_dir_name="${type}v${new_version}"
    mkdir "$new_dir_name" && cd "$new_dir_name"
    echo "$new_dir_name"
}

# Determine the latest version and create a new version directory
latest_version=$(get_latest_version)
version_dir=$(create_version_directory $latest_version)

cd "$version_dir" || exit 1

   # Securely copy config file into folder
sshpass -v -p "$devPass" scp -o StrictHostKeyChecking=no "$devMachineName@$devIP:$path/$configFile" "./$configFile"
echo "SCP command completed."

# Read config file into array
IFS=$'\n' read -d '' -r -a lines < "$configFile"
    
# Extract package info from config
pkgName=${lines[2]}
installLoc=${lines[5]}
services=${lines[7]}
length=${#lines[@]}
#Change path here depending on files location on dev machine 
path="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
# Copy the PHP files using the correct path
for ((i=9; i<length; i++)); do
    echo "Copying ${lines[i]} from dev..."
    sshpass -v -p "$devPass" scp "$devMachineName@$devIP:$path/${lines[i]}" "./${lines[i]}"
done

# Zip files excluding the config
zip -r -j "$pkgName.zip" ./* -x "*.config"
echo "Package $pkgName.zip created."

# Clean up the unzipped files
echo "Cleaning up unzipped files..."
find . -type f ! -name "$pkgName.zip" -delete
echo "Cleanup complete."

    # Insert version info into database
    version_number=$(echo "$version_dir" | grep -oP '(?<=v)\d+$')
    mysql --user="$user" --password="$password" --database="$database" -e "INSERT INTO versionHistory (version, pkgName, passed) VALUES ('$version_number', '$version_dir', NULL);"
    echo "Version: $version_number pushed with package name: $pkgName"
