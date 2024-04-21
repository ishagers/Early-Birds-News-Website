<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files for RabbitMQ
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function publisher($array)
{
    try {
        // Create a new client instance
        $client = new rabbitMQClient("SQLServer.ini", "SQLServer");
        
        // Send the request and receive response
        $response = $client->send_request($array);
        
        if (!$response) {
            throw new Exception('No response from server.');
        }

        return $response;
    } catch (Exception $e) {
        // Log and/or handle errors appropriately
        error_log("Error in publisher function: " . $e->getMessage());
        return null; 
    }
}

