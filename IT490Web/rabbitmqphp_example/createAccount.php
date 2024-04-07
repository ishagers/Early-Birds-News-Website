<?php
require("session.php");
require('SQLPublish.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_POST['new_username']) && !empty($_POST['new_password']) && !empty($_POST['name']) && !empty($_POST['email'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) { //MAKE SURE PASSWORDS ARE THE SAME
        echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='accountCreation.php';</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //HASH PASSWORD

        //Setting array and its values to send to RabbitMQ
        $queryValues = array();

        $queryValues['type'] = 'create_account';
        $queryValues['username'] = $username;
        $queryValues['password'] = $hashedPassword;
        $queryValues['name'] = $name;
        $queryValues['email'] = $email;

        //Printing Array and executing SQL Publisher function
        //print_r($queryValues);
        $result = publisher($queryValues);

        //If returned 0, it means it was pushed to the database. Otherwise, echo error
        if ($result == 0) {
            $_SESSION['username'] = $username; // Set the username in the session
            // Use JavaScript for redirect to ensure the alert is shown before redirecting
            echo "<script>alert('User Created Successfully!'); window.location.href = '../index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Account Created! Please Sign in.'); window.location.href='../index.php';</script>";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="../routes/styles.css" />
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type" />
    <meta content="utf-8" http-equiv="encoding" />
</head>
<body>
    <div class="container">
        <div class="title">Create Account</div>
        <form method="post">
            <p>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required />
            </p>

            <p>
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required />
            </p>

            <p>
                <label for="new_username">Username <Bold>This version is Bad, rollback<Bold></label>
                <input type="text" id="new_username" name="new_username" required />
            </p>

            <p>
                <label for="new_password">Password</label>
                <input type="password" id="new_password" name="new_password" required />
            </p>

            <p>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required />
            </p>

            <p>
                <button type="submit">Create Account</button>
            </p>
        </form>
    </div>
</body>
</html>
