<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $organization_account = $_POST['organization_account'];
    $recipient_account = $_POST['recipient_account'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $user_id = $_POST['user_id']; // This would be the recipient (employee or supplier)

    if (empty($organization_account) || empty($recipient_account) || empty($amount) || empty($type) || empty($date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Payments (organization_account, recipient_account, amount, user_id, type, date) VALUES (:organization_account, :recipient_account, :amount, :user_id, :type, :date)");
        $stmt->execute([
            ':organization_account' => $organization_account,
            ':recipient_account' => $recipient_account,
            ':amount' => $amount,
            ':user_id' => $user_id,
            ':type' => $type,
            ':date' => $date
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Payment created successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
