<?php
session_start();

// Check if the user is authenticated
if (isset($_SESSION['user'])) {
    // If the user is logged in, redirect them to the dashboard
    header('Location: /pages/dashboard.php');
    exit();
} else {
    // If the user is not logged in, redirect them to the login page
    header('Location: /pages/login.php');
    exit();
}
