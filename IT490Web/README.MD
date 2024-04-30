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

OS: Ubuntu, create a VM and dedicate at least 4 GiB of ram and 4 cores and a minimum of 30 GB of allocated storage. If you have more resources than that, the more the better but keep in mind there will be many VMs running at the same time.
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




#Setting up BackEnd 
    -At some point throughout this project, when working with clusters, whoever is working on the BackEnd will have to take over the database.
    - Implement firewall rules to protect the database and the backend
    - Define your programming language of choice. It is recommended to use PHP and JavaScript for simplicity.

#Database


#DMZ

- All of the files within the Git Repo must be moved into var/www/html

- Access the DMZServer.ini within the rabbitmq directory

- Edit the DMZServer.ini and set the BROKER_HOST IP to the relative Backend VM’s ZeroTier IP. (So if it is a Development DMZ like in our case; Set the BROKER_HOST to the Development Backend’s ZeroTier IP)
    #these steps are optional as they are to automate the DMZ, otherwise you can just Start the DMZServer.php everytime you spin up the VM.
- Edit the DMZServer.service file and change the execution path to where DMZServer.php is on your machine (Typically in var/www/html/"folderName"), in our case the backend vm doesn't run the apache server locally so when that VM spins up, the service gets called.

- Copy DMZServer.service to etc/systemd/system

- Start the DMZServer.php or set up a cron tab to run it everyttime it turns off.
