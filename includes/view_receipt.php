<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_id = $_POST['id'];

    try {
        // Fetch the receipt details
        $stmt = $pdo->prepare("
            SELECT Receipts.id AS receipt_id, Receipts.amount, Receipts.date, 
                   Transactions.id AS transaction_id, Users.username 
            FROM Receipts
            INNER JOIN Transactions ON Receipts.transaction_id = Transactions.id
            INNER JOIN Users ON Receipts.user_id = Users.id
            WHERE Receipts.id = :id
        ");
        $stmt->bindParam(':id', $receipt_id, PDO::PARAM_INT);
        $stmt->execute();

        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            echo json_encode(['status' => 'error', 'message' => 'Receipt not found.']);
            exit();
        }

        // Prepare the HTML to display receipt details in the modal
        $html = "
            <p><strong>Receipt ID:</strong> {$receipt['receipt_id']}</p>
            <p><strong>Transaction ID:</strong> {$receipt['transaction_id']}</p>
            <p><strong>User:</strong> {$receipt['username']}</p>
            <p><strong>Amount:</strong> {$receipt['amount']}</p>
            <p><strong>Date:</strong> " . date('Y-m-d H:i:s', strtotime($receipt['date'])) . "</p>
        ";

        echo json_encode(['status' => 'success', 'html' => $html]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
