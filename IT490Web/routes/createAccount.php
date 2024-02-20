<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password === $confirmPassword) {
        // Insert the new account into DB
        // Redirect to login page
    } else {
        echo "<script>
            alert('Passwords do not match. Please try again.');
            window.location.href='createAccount.html';
            </script>";
        exit();
    }
}
?>
