<?php

// Database connection details as constants
define('DB_SERVER', '10.147.17.233');
define('DB_USERNAME', 'IT490DB');
define('DB_PASSWORD', 'IT490DB');
define('DB_DATABASE', 'EARLYBIRD');

function getDatabaseConnection()
{
    static $conn = null; // Static variable to hold the connection

    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error connecting to database: " . $e->getMessage());
        }
    }

    return $conn;
}

function createUser($name, $username, $email, $hash, $role)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Use the single connection function

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
    }

    // Return the response array
    return $response;
}

function login($username, $password)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // SQL statement to select user by username
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);

        // Execute the statement
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['hash'])) {
                // Password is correct
                $response['status'] = true;
                $response['message'] = "Login successful";
            } else {
                // Password is incorrect
                $response['message'] = "Invalid username or password";
            }
        } else {
            // Username does not exist
            $response['message'] = "Invalid username or password";
        }
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}

