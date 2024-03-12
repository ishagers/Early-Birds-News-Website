<?php
$servername = "10.147.17.233"; // ZeroTier IP of the database VM
$username = "IT490DB"; // MySQL username
$password = "IT490DB"; // MySQL password
$dbname = "EARLYBIRD"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>


