#!/bin/bash

# MySQL login and db stuff
user='IT490DB'
password='IT490DB'
database='Deployment'

echo "Please enter the Bundle Name/version (example FEv2): "
read version

echo "Did it pass or fail? [pass/fail]: "
read passFail

# Sanitize input for 'passFail' to ensure correct database operation
if [ "$passFail" == "pass" ]; then
    passOrFail=1
    echo "Marking $version as PASSED..."
elif [ "$passFail" == "fail" ]; then
    passOrFail=0
    echo "Marking $version as FAILED..."
else
    echo "Invalid input. Please enter 'pass' or 'fail'."
    exit 1
fi

# Execute the MySQL command
mysql --user="$user" --password="$password" --database="$database" -e "UPDATE versionHistory SET passed=$passOrFail WHERE pkgName='$version';"

 if [ $? -eq 0 ]; then
    echo "Database updated successfully."
    if [ "$passOrFail" -eq 1 ]; then
        echo "Initiating automatic deployment to Production VM..."
        PROD_INSTALL_SCRIPT_PATH="/var/www/html/IT490-Project/IT490Web/Deployment/ProdInstall.sh"
        EXPECT_SCRIPT_PATH="/var/www/html/IT490-Project/IT490Web/Deployment/runProdInstall.expect"
        PROD_MACHINE_IP="10.147.17.206"
        PROD_MACHINE_USER="juanguti"
        bundleType=${version%%v*}
        # Pass the required arguments to the expect script
        /usr/bin/expect -f "$EXPECT_SCRIPT_PATH" "$PROD_MACHINE_USER" "$PROD_MACHINE_IP" "$PROD_INSTALL_SCRIPT_PATH" "FE" "$bundleType" "$version"
        
        echo "Production deployment initiated."
    fi
else
    echo "Failed to update the database."
fi