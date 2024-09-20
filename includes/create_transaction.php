<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and validate POST data
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : json_decode($_SESSION['user'])->id; // Use posted user_id if admin, otherwise session user_id
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate input fields
    if (empty($amount) || empty($type) || empty($description)) {
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
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (:user_id, :amount, :type, :description)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':amount' => $amount,
            ':type' => $type,
            ':description' => $description
        ]);

        // Check if the insertion was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction created successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create transaction.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error occurred: ' . $e->getMessage()]);
    }

    exit();
}
?>
