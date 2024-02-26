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
echo "<p>Connected successfully</p>";

$sql = "SELECT * FROM users"; // Note: Corrected the SQL statement by removing the comment syntax
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Start table
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th></tr>"; // Adjust the column headers as needed

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["username"] . "</td><td>" . $row["email"] . "</td></tr>"; // Adjust to match your column names
    }

    // End table
    echo "</table>";
} else {
    echo "<p>0 results</p>";
}

// Close connection
$conn->close();
?>
