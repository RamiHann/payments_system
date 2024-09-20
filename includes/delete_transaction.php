<?php
include_once '../config/config.php';
include_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit();
    }

    try {
        // Delete transaction
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete transaction']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
