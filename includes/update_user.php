<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($username) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Check if the username already exists (excluding the current user)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
    $checkStmt->bindValue(':username', $username);
    $checkStmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $checkStmt->execute();
    $userCount = $checkStmt->fetchColumn();

    if ($userCount > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        exit();
    }

    // Update the user
    $stmt = $pdo->prepare("UPDATE users SET username = :username, role = :role WHERE id = :id");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':role', $role);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user.']);
    }
}
?>
