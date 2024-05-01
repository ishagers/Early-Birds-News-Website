# Early Birds
    Members:
    [Angel Espinal](https://github.com/ishagers)
    [Juan Gutierrez](https://github.com/JuanPGutierrezz)

#Project Documentation

#Apps you need for this project:

- You need any software type hypevisor for simplicity : Oracle VirtualBox, VMWare, UTM.
- You need a github account : GitHub.
- You need a project management tool : Trello, Jira, Asana.
- Seed Project : https://github.com/ishagers/IT490-Project
- Ubuntu 20.04 LTS image or better
- ZeroTier VPN or any other network virtualization platform to interconnect the vms.
- An API to fetch the data from: Ours is about news so any news API should suffice.

#After getting everything, for each VM include the following:

OS: Ubuntu, create a VM and dedicate at least 4 GiB of ram and 4 cores and a minimum of 30 GB of allocated storage. If you have more resources than that, the more the better but keep in mind there will be many VMs running at the same time. It is advised to increase the ram allocation to at least 8 Gb down the road as the project gets bigger.
    #Install the following:
    - UFW
    - Vim or any other code/ text editor
    - Php
	- Php-amqplib
	- Rabbitmq-server
	- Mysql-server
	- Apache2
	- Zerotier-cli
	- Openssl
    - Curl
Once everything has been installed. Connect each VM to the network. In our case, connect them all to the zerotier network and enable the rabbitMQ management plugin

#How To Setup Development, Quality Assurance, Production Clusters:

#Setting up Frontend
- Make sure apache 2 is installed and running

- under /etc/apache2/ports.conf, make sure the line 'Listen 8000' exists

- Install PHP version 8.1 and configure it to work with apache
    #make sure to enable the module via sudo a2enmod php8.1

- We installed openSSL for HTTPS

- Set up SSL credentials for HTTPS

- Configure firewall to allow ports 22, 80, and 443

- Moved the github repo into the /var/www/html folder

- Website access via <Web Server IP>:8000/IT490-Project/IT490Web/index.php

- Important to restart apache after every change made to apache2 files or installations

- Not necessary, but we used auto pull command script attached to the front end for ease of remote work


#Setting up BackEnd 
    -At some point throughout this project, when working with clusters, whoever is working on the BackEnd will have to take over the database.

    - Implement firewall rules to protect the database and the backend

    - If you are also hosting rabbitMQServer, set the RabbitMQServer.ini file with your ZeroTier Credentials.

    - Do the same with the database SQLServer.ini file

    - Go to RMQ Management website and create channel, queue and the exchange.

    - Define your programming language of choice. It is recommended to use PHP and JavaScript for simplicity.

    - Create a script via cron to fetch news every X amount of hours.

#Database
 - If using a brand new database, create access credentials and data tables. Make sure to assign your ZeroTier IP.

 - Access the Earlybirds database via mysql to view all tables (mysql password & user are 'IT490DB')

 - Change ip permissions within mysql to new machine when creating new clusters or layers

#DMZ

- All of the files within the Git Repo must be moved into var/www/html

- Access the DMZServer.ini within the rabbitmq directory

- Edit the DMZServer.ini and set the BROKER_HOST IP to the relative Backend VM’s ZeroTier IP. (So if it is a Development DMZ like in our case; Set the BROKER_HOST to the Development Backend’s ZeroTier IP)
    #these steps are optional as they are to automate the DMZ, otherwise you can just Start the DMZServer.php everytime you spin up the VM.
- Edit the DMZServer.service file and change the execution path to where DMZServer.php is on your machine (Typically in var/www/html/"folderName"), in our case the backend vm doesn't run the apache server locally so when that VM spins up, the service gets called.

- Copy DMZServer.service to etc/systemd/system

- Start the DMZServer.php or set up a cron tab to run it everyttime it turns off.

#Deployment
- Make sure UFW permissions are properly set to interact with other VMs via ssh 

- Access Deployment database and version history table via user and passwords "IT490DB"
    #This database table handles the version, bundlename, and pass/fail of version that is rollout

- Install expect tool for deployent automation

- Verify IP, hostname,password, and file paths within each cluster within the deployment files to align.
    #the file paths should be the same if everything is replicated the same, just change the IP, hostname,password from the .sh deployment scripts.


