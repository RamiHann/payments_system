<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and validate POST data
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : intval(json_decode($_SESSION['user'])->id);
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate input fields
    if ($transaction_id <= 0 || empty($amount) || empty($type) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Validate amount to ensure it is a positive number
    if (!is_numeric($amount) || floatval($amount) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Amount must be a positive number.']);
        exit();
    }

    // Validate type
    $valid_types = ['debit', 'credit']; // Define valid types
    if (!in_array($type, $valid_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid transaction type.']);
        exit();
    }

    // Prepare and execute SQL query
    try {
        // Check if the transaction exists
        $stmt_check = $pdo->prepare("SELECT id FROM transactions WHERE id = :id");
        $stmt_check->execute([':id' => $transaction_id]);

        if ($stmt_check->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found.']);
            exit();
        }

        // Update the transaction
        $stmt_update = $pdo->prepare("UPDATE transactions SET user_id = :user_id, amount = :amount, type = :type, description = :description WHERE id = :id");
        $stmt_update->execute([
            ':user_id' => $user_id,
            ':amount' => $amount,
            ':type' => $type,
            ':description' => $description,
            ':id' => $transaction_id
        ]);

        // Send success response
        echo json_encode(['status' => 'success', 'message' => 'Transaction updated successfully.']);
    } catch (PDOException $e) {
        // Send error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to update transaction: ' . $e->getMessage()]);
    }

    exit();
}
