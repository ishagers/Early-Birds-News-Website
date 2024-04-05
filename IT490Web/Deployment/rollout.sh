#!/bin/bash/

#set a version variable
version=1

#mysql login and db stuff
user=bash
password=1234
database=deployment

#get basic info file
echo "Please enter the dev machine(FE, BE, DMZ): "
read machine
#echo "Where did it install?(FE, BE, DMZ)?: $location"
echo "Please Enter the config File Name: "
read configFile
#="frontEndFiles.config"
#set the path for where everyone keeps their files

if [ "$machine" == "FE" ]; then
    path="/var/www/html/IT490-Project/IT490Web/rabbitmqphp_example"
    devMachineName="gds"
    devIP="172.27.35.201"
    devPass="R0seli197$"

elif [ "$machine" == "BE" ]; then
    path="/var/www/html"
    devMachineName="dran"
    devIP="172.27.63.249"
    devPass="pharmacy"

elif [ "$machine" == "DMZ" ]; then
    path="/var/www/html"
    devMachineName="bsingh"
    devIP="172.28.125.110"
    devPass="05072000"
else
    echo "Error: Invalid machine type specified. Please enter 'FE', 'BE', or 'DMZ'."
    exit 1
fi


while :
do
    if [ ! -d "v$version" ]; then
    mkdir "v$version"
    cd v$version
    
    #copy config file into folder
    sshpass -v -p $devPass scp $devMachineName@$devIP:$path/$configFile ~/deployment/v$version/$configFile 
    #read file into array
    IFS=$'\n' read -d '' -r -a lines < $configFile
    #get each file outlined in config
    pkgName=${lines[2]}
    installLoc=${lines[5]}
    qaMachine=${lines[4]}
    services=${lines[7]}
    length=${#lines[@]}
    for ((i=9; i<${length}; i++));
        do
            echo copying ${lines[i]} from dev...
            sshpass -v -p $devPass scp $devMachineName@$devIP:$path//${lines[i]} ~/deployment/v$version/${lines[i]} 
        done


    #zip files
    zip -r -j $pkgName ~/deployment/v$version/* -x "*.config"

    #set QA Machine paths and IP
    if [ $qaMachine == "FE" ]; then
        QAMachineName="gds25"
        QAIP="172.27.249.118"
        QAPass='R0seli197$'

        #remove files from qa
        for ((i=9; i<${length}; i++));
            do
                echo deleting ${lines[i]} from QA...
                ssh gds25@172.27.35.201 "rm -r $installLoc/${lines[i]}"
            done
        
        #send to QA(testqa)
        echo sending $pkgName to QA...
        sshpass -v -p 'R0seli197$' scp ~/deployment/v$version/$pkgName.zip gds25@172.27.249.118:$installLoc
        ssh gds25@172.27.249.118 "unzip $installLoc/$pkgName.zip -d $installLoc"
        ssh gds25@172.27.249.118 "rm -r $installLoc/$pkgName.zip"
        echo Pushed Version: $version 
        #mysql update table

        mysql --user="$user" --password="$password" --database="$database" --execute="INSERT INTO versionHistory (version, pkgName, passed) VALUES ($version, \"$pkgName\", NULL);"
        
        #restart any services based on config 

        if [ $services == "apache" ]; then
            #FE: apache 
            echo 'R0seli197$' | ssh -t -t gds25@172.27.249.118 "sudo systemctl restart apache2"
            echo apache restarted
        elif [ $services == "databaseServer" ]; then
            #BE: DBServer.php
            echo 'R0seli197$' | ssh -t -t gds25@172.27.249.118 "sudo systemctl restart DatabaseService.service"
            echo "Database Server restarted :)"
            echo Database Server restarted
        elif [ $services == "databaseServer" ]; then
            #BE: Mysql
            echo 'pharmacy' | ssh -t -t gds25@172.27.249.118 "sudo systemctl restart mysql"
            echo mysql restarted
        elif [ $services == "DMZServer" ]; then
            DMZ: DMZServer.php
            echo 'R0seli197$' | ssh -t -t gds25@172.27.249.118 "sudo systemctl restart DMZService.service"
            echo "DMZ Server restarted :)"
        fi

else
    if [ $qaMachine == "BE" ]; then
        QAMachineName="dran"
        QAIP="172.27.34.208"
        QAPass='pharmacy'

        #remove files from qa
        for ((i=9; i<${length}; i++));
            do
                echo deleting ${lines[i]} from QA...
                ssh dran@172.28.231.181 "rm -r $installLoc/${lines[i]}"
            done
        
        #send to QA(testqa)
        echo sending $pkgName to QA...
        sshpass -v -p 'pharmacy' scp ~/deployment/v$version/$pkgName.zip testqa@172.27.34.208:$installLoc
        ssh dran@172.27.34.208 "unzip $installLoc/$pkgName.zip -d $installLoc"
        ssh dran@172.27.34.208 "rm -r $installLoc/$pkgName.zip"
        echo Pushed Version: $version 
        #mysql update table

        mysql --user="$user" --password="$password" --database="$database" --execute="INSERT INTO versionHistory (version, pkgName, passed) VALUES ($version, \"$pkgName\", NULL);"
        
        #restart any services based on config 

        if [ $services == "apache" ]; then
            #FE: apache 
            echo 'pharmacy' | ssh -t -t dran@172.27.34.208 "sudo systemctl restart apache2"
            echo apache restarted
        elif [ $services == "databaseServer" ]; then
            #BE: DBServer.php
            echo 'pharmacy' | ssh -t -t dran@172.27.34.208 "sudo systemctl restart DatabaseService.service"
            echo "Database Server restarted :)"
            echo Database Server restarted
        elif [ $services == "databaseServer" ]; then
            #BE: Mysql
            echo 'pharmacy' | ssh -t -t dran@172.27.34.208 "sudo systemctl restart mysql"
            echo mysql restarted
        elif [ $services == "DMZServer" ]; then
            DMZ: DMZServer.php
            echo 'pharmacy' | ssh -t -t dran@172.27.34.208 "sudo systemctl restart DMZService.service"
            echo "DMZ Server restarted :)"
        fi
    else
        #is DMZ
        QAMachineName="bsingh"
        QAIP="172.27.118.99"
        QAPass='05072000'

        #remove files from qa
        for ((i=9; i<${length}; i++));
            do
                echo deleting ${lines[i]} from QA...
                ssh bsingh@172.27.118.99 "rm -r $installLoc/${lines[i]}"
            done
        
        #send to QA(testqa)
        echo sending $pkgName to QA...
        sshpass -v -p '05072000' scp ~/deployment/v$version/$pkgName.zip bsingh@172.27.118.99:$installLoc
        ssh bsingh@172.27.118.99 "unzip $installLoc/$pkgName.zip -d $installLoc"
        ssh bsingh@172.27.118.99 "rm -r $installLoc/$pkgName.zip"
        echo Pushed Version: $version 
        #mysql update table

        mysql --user="$user" --password="$password" --database="$database" --execute="INSERT INTO versionHistory (version, pkgName, passed) VALUES ($version, \"$pkgName\", NULL);"
        
        #restart any services based on config 

        if [ $services == "apache" ]; then
            #FE: apache 
            echo '05072000' | ssh -t -t bsingh@172.27.118.99 "sudo systemctl restart apache2"
            echo apache restarted
        elif [ $services == "databaseServer" ]; then
            #BE: DBServer.php
            echo '05072000' | ssh -t -t bsingh@172.27.118.99 "sudo systemctl restart DatabaseService.service"
            echo "Database Server restarted :)"
            echo Database Server restarted
        elif [ $services == "databaseServer" ]; then
            #BE: Mysql
            echo '05072000' | ssh -t -t bsingh@172.27.118.99 "sudo systemctl restart mysql"
            echo mysql restarted
        elif [ $services == "DMZServer" ]; then
            DMZ: DMZServer.php
            echo '05072000' | ssh -t -t bsingh@172.27.118.99 "sudo systemctl restart DMZService.service"
            echo "DMZ Server restarted :)"
        fi
    fi
fi
    
    
        
        
    break 
    else
        let "version=version+1"
    fi

    
done



