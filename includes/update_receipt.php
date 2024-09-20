<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_id = $_POST['id'];
    $transaction_id = $_POST['transaction_id'];
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];

    if (empty($transaction_id) || empty($user_id) || empty($amount)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Receipts SET transaction_id = :transaction_id, user_id = :user_id, amount = :amount WHERE id = :id");
        $stmt->execute([
            ':transaction_id' => $transaction_id,
            ':user_id' => $user_id,
            ':amount' => $amount,
            ':id' => $receipt_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Receipt updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
