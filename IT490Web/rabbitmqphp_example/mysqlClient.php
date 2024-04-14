<?php
// Database connection settings
$host = '10.147.17.233'; // MySQL server IP or hostname
$dbname = 'EARLYBIRD'; // Database name
$user = 'IT490DB'; // Database username
$pass = 'IT490DB'; // Database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Connected successfully"; 
} catch(PDOException $e) {
    error_log($e->getMessage());
    die("Connection failed: " . $e->getMessage()); 
}

?>

