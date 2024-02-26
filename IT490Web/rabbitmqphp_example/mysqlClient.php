#!/usr/bin/php
<?php
// MySQL connection parameters
$servername = "10.147.17.233"; // Your MySQL server host
$username = "IT490DB"; // Your MySQL username
$password = "IT490DB"; // Your MySQL password
$database = "EARLYBIRD"; // Your MySQL database name
$port = "3306"; //Port

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully\n";

$sql = "SELECT * FROM users WHERE username='dog'"; //Testing
$result = $conn->query($sql);

echo "Find results:\n";
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        var_dump($row);
    }
} else {
    echo "0 results";
}

// Close connection
$conn->close();
?>
