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

# Function to increment version number and find a non-existing directory
increment_version() {
    while [ -d "v$version" ]; do
        let "version=version+1"
    done
    mkdir "v$version" && cd "v$version"
}

# Call the increment_version function to find the correct version directory to create
increment_version

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

    path="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"

    # Now the loop that copies the PHP files will use the correct path
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

# Deploy package to QA machine and restart services
sshpass -p "$devPass" scp -o StrictHostKeyChecking=no "$pkgName.zip" "$devMachineName@$qaIP:$installLoc"

# Remove old package files first
sshpass -p "$devPass" ssh -o StrictHostKeyChecking=no "$devMachineName@$qaIP" "rm -rf $installLoc/*"

# Unzip new package
sshpass -p "$devPass" ssh -o StrictHostKeyChecking=no "$devMachineName@$qaIP" "unzip $installLoc/$pkgName.zip -d $installLoc && rm $installLoc/$pkgName.zip"

IFS=',' read -r -a servicesArray <<< "$services"
for service in "${servicesArray[@]}"; do
    echo "Attempting to restart $service on QA machine..."
    sshpass -p "$devPass" ssh -o StrictHostKeyChecking=no "$devMachineName@$qaIP" "sudo systemctl restart $service"
done

    let "version=version+1"
