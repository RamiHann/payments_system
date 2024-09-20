<?php
session_start();

// Check if the user is logged in
function isAuthenticated() {
    return isset($_SESSION['user']);
}

// Redirect unauthenticated users
function requireLogin() {
    if (!isAuthenticated()) {
        header("Location: /login.php");
        exit;
    }
}
?>
