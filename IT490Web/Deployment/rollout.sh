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
echo "Please Enter the config File Name: "
read configFile

# Set configuration based on machine type
case $machine in
    "FE")
        path="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
        devMachineName="juanguti"
        devIP="10.147.17.233"
        devPass="YogiMaster123@"
        echo "Read FE machine details"
        ;;
    "BE")
        path="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
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

# Ensure version directory doesn't already exist
if [ ! -d "v$version" ]; then
    mkdir "v$version" && cd "v$version" || exit
            echo "Passed first if statement (version check)"
    # Securely copy config file into folder
sshpass -v -p "$devPass" scp "$devMachineName@$devIP:$path/$configFile" "./$configFile"
echo "SCP command completed."

    # Read config file into array
    IFS=$'\n' read -d '' -r -a lines < "$configFile"
    
    # Extract package info from config
    pkgName=${lines[2]}
    installLoc=${lines[5]}
    qaMachine=${lines[4]}
    services=${lines[7]}
    length=${#lines[@]}

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

else
    let "version=version+1"
fi
