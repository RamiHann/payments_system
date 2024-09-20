<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];

    if (empty($transaction_id) || empty($user_id) || empty($amount)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Receipts (transaction_id, user_id, amount) VALUES (:transaction_id, :user_id, :amount)");
        $stmt->execute([
            ':transaction_id' => $transaction_id,
            ':user_id' => $user_id,
            ':amount' => $amount
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Receipt created successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
