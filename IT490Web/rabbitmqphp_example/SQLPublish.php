<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function publisher($array)
{
    $client = new rabbitMQClient("SQLServer.ini", "SQLServer");
    $response = $client->send_request($array);

    // Assuming the response is an array and contains a 'status' key
    if (is_array($response)) {
        // Check if the operation was successful
        if (isset($response['status']) && $response['status'] == 'success') {
            // Return a simple success message or boolean
            return true;
        } else {
            // Return a simple error message or boolean
            return false;
        }
    } else {
        // Handle unexpected response format
        // Log the unexpected response for debugging
        error_log("Unexpected response format: " . print_r($response, true));

        // Return a generic error or boolean to indicate failure
        return false;
    }
}