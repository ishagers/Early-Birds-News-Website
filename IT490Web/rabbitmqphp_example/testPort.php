$host = '10.147.17.178'; // The host IP where RabbitMQ server is running
$port = 5672; // The port RabbitMQ is listening on
$timeout = 2; // Timeout in seconds

$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (is_resource($connection)) {
    echo "Port " . $port . " on host " . $host . " is open.\n";
    fclose($connection); // Close the connection when done
} else {
    echo "Port " . $port . " on host " . $host . " is not responding.\n";
    echo "Error: " . $errstr . " [" . $errno . "]\n";
}
