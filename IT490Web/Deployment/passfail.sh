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
else
    echo "Failed to update the database."
fi