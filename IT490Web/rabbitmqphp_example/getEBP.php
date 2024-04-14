<?php
session_start();
require_once 'databaseFunctions.php';

if (isset($_SESSION['username'])) {
    echo fetchUserEBP($_SESSION['username']);
} else {
    echo "0";
}