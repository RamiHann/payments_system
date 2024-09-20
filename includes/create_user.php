<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Check if username already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $checkStmt->bindValue(':username', $username);
    $checkStmt->execute();
    $userCount = $checkStmt->fetchColumn();

    if ($userCount > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the new user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':role', $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add user.']);
    }
}
?>
