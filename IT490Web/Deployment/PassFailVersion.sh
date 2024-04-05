#!/bin/bash
# MySQL login and db stuff
user='IT490DB'
password='IT490DB'
database='Deployment'

echo "Please enter the version number: "
read version

echo "(pass) OR (fail)"
read passFail

# Function to execute commands on the production machine
execute_commands() {
    echo "deleting ${lines[i]} from QA..."
    ssh "$ProdMachineName@$ProdIP" "rm -r $installLoc/${lines[i]}"
    
    echo "sending $pkgName to QA..."
    sshpass -p "$ProdPass" scp ~/deployment/v$version/$pkgName.zip "$ProdMachineName@$ProdIP:$installLoc"
    ssh "$ProdMachineName@$ProdIP" "unzip $installLoc/$pkgName.zip -d $installLoc && rm -r $installLoc/$pkgName.zip"
    
    echo "Pushed Version: $version"

    # Restart services
    for service in $services; do
        echo "Restarting $service..."
        ssh "$ProdMachineName@$ProdIP" "sudo systemctl restart $service"
    done
}

# Update database to PASSED or FAILED
update_database() {
    passOrFail=$1
    newState=$2
    echo "updating database to $newState..."
    mysql --user="$user" --password="$password" --database="$database" -e "UPDATE versionHistory SET passed = $passOrFail WHERE version = $version;"
    echo "Database updated to $newState for version $version."
}

# Function to perform rollback
perform_rollback() {
    # Retrieve the last known good version
    lastGoodVersion=$(mysql --user="$user" --password="$password" --database="$database" -sse "SELECT MAX(version) FROM versionHistory WHERE passed = True AND pkgName = '$pkgName';")

    # Check if we got a valid last good version
    if [ -z "$lastGoodVersion" ]; then
        echo "No last good version to roll back to."
        exit 1
    fi

    echo "Rolling back to the last passed version ($lastGoodVersion)..."

    # Remove current files and replace with files from the last good version
    for ((i=9; i<${#lines[@]}; i++)); do
        echo "Deleting ${lines[i]} from $prodMachine..."
        ssh "$ProdMachineName@$ProdIP" "rm -r $installLoc/${lines[i]}"
        echo "Restoring ${lines[i]} from version $lastGoodVersion..."
        sshpass -p "$ProdPass" scp "~/deployment/v$lastGoodVersion/${lines[i]}" "$ProdMachineName@$ProdIP:$installLoc"
    done

    # Restart services if necessary
    for service in $services; do
        echo "Restarting $service..."
        ssh "$ProdMachineName@$ProdIP" "sudo systemctl restart $service"
    done

    echo "Rollback to version $lastGoodVersion completed."
}

# Main logic
case $passFail in
    "p")
        update_database 'True' 'PASSED'
        # Retrieve package name from database
        pkgName=$(mysql --user="$user" --password="$password" --database="$database" -sse "SELECT pkgName FROM versionHistory WHERE version = $version;")
        
        # Read the config file into an array
        IFS=$'\n' read -d '' -r -a lines < "v$version/$pkgName.config"
        installLoc=${lines[5]}
        ProdMachineName=${lines[4]}
        services=${lines[7]}
        
        # Determine which production machine to use
        case $ProdMachineName in
            "FE")
                ProdMachineName="juanguti"
                ProdIP="10.147.17.233"
                ProdPass="YogiMaster123@"
                ;;
            "BE")
                ProdMachineName="ANGELTI490DEVUSERMACHINE"
                ProdIP="10.147.17.90"
                ProdPass="ANGELIT490DEVPASSWORD"
                ;;
            "DMZ")
                ProdMachineName="AngelDMZ490"
                ProdIP="10.147.17.227"
                ProdPass="dmz490"
                ;;
            *)
                echo "Error: Invalid production machine specified."
                exit 1
                ;;
        esac
        
        # Execute commands on the production machine
        execute_commands
        ;;
     "f")
        update_database 'False' 'FAILED'
        # Retrieve package name from the failed version
        pkgName=$(mysql --user="$user" --password="$password" --database="$database" -sse "SELECT pkgName FROM versionHistory WHERE version = $version;")
        # Call rollback function
        perform_rollback
        ;;
    *)
        echo "Invalid input. Please enter 'p' for pass or 'f' for fail."
        ;;
esac
