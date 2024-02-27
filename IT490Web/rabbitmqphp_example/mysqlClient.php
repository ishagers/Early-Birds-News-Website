<?php
echo "Script started.<br>";

// MySQL connection parameters
$servername = "10.147.17.233";
$username = "IT490DB";
$password = "IT490DB";
$database = "EARLYBIRD";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo "Connection to DB Failed.<br>";
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to database successfully.<br>";
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result === false) {
    echo "Error executing query: " . $conn->error . "<br>";
} else {
    if ($result->num_rows > 0) {
        // Process your results here
        echo "Query executed successfully, data retrieved.<br>";
    } else {
        echo "Query executed successfully, but no data found.<br>";
    }
}

// Close connection
$conn->close();
?>
