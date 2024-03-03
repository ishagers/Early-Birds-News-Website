<?php
    session_start();

    if(isset($_SESSION ['username'])){
        echo "<p> Logged in as: " . $_SESSION['username']. "</p>";
        echo '<p><a href = "../index.php">Log Out</a></p>';
    }

    function checkLogin(){
        if(!isset($_SESSION['username'])){
            echo "<script>alert('Please log in first!')</script>";
            header("Refresh: .1; url=../index.php");
        }
    }
?>