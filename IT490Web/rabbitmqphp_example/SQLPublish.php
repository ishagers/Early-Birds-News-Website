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
    return $response;
}
