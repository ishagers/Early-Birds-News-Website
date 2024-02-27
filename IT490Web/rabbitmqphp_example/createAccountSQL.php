<?php
function createUser($name, $username, $email, $hash, $role)
{
    // Database connection details
    $servername = "10.147.17.233";
    $dbUsername = "IT490DB";
    $dbPassword = "IT490DB";
    $database = "EARLYBIRD";

    // Initialize the response array
    $response = array('status' => false, 'message' => '');

    try {
        // Establishing the database connection
        $conn = new PDO("mysql:host=$servername;dbname=$database", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL statement to insert a new user
        $sql = "INSERT INTO users (name, username, email, hash, role) VALUES (:name, :username, :email, :hash, :role)";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':role', $role);

        // Execute the statement
        $stmt->execute();

        // Update response status and message on success
        $response['status'] = true;
        $response['message'] = "New user created successfully";
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Error: " . $e->getMessage();
    } finally {
        // Close connection
        if ($conn) {
            $conn = null;
        }
    }

    // Return the response array
    return $response;
}

