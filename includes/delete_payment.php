<?php
require_once('../config/config.php');

$id = $_POST['id'];

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided.']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM Payments WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Payment deleted successfully.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
