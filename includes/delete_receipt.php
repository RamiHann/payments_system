<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_id = $_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM Receipts WHERE id = :id");
        $stmt->bindParam(':id', $receipt_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Receipt deleted successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting receipt: ' . $e->getMessage()]);
    }
}
