<?php
// MySQL connection parameters
$servername = "10.147.17.233";
$username = "IT490DB"; 
$password = "IT490DB"; 
$database = "EARLYBIRD"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

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
