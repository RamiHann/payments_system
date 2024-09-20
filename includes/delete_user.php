<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['id'] ?? 0;

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete user.']);
    }
}
?>
