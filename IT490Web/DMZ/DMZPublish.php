#!/usr/bin/php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
  
 
  $client = new rabbitMQClient("DMZServer.ini","DMZServer");
  $response = $client->send_request($argv[1]);
  
  //Establish Connection
  if(isset($argv[2])){
    print_r($response);
    exit();
  }
  $mysql = new mysqli('localhost', 'dran', 'pharmacy', 'animeDatabase');
  if ($mysql -> connect_errno){
      return "Could not connect to mysql: ". $mysql->connect_error;
      exit();
  }

  $mysql->close();
  exit();
?>

