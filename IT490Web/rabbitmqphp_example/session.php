<?php
    session_start();
    var_dump($_POST)
    function checkLogin(){
        if(!isset($_SESSION['username'])){
            echo "<script>alert('Please log in first!')</script>";
            header("Refresh: .1; url=../index.php");
        }
    }
