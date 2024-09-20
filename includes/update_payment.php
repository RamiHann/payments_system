<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

$payment_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $organization_account = $_POST['organization_account'];
    $recipient_account = $_POST['recipient_account'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $date = $_POST['date'];
    $user_id = $_POST['user_id'];

    if (empty($organization_account) || empty($recipient_account) || empty($amount) || empty($type) || empty($date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Payments SET organization_account = :organization_account, recipient_account = :recipient_account, amount = :amount, user_id = :user_id, type = :type, date = :date WHERE id = :id");
        $stmt->execute([
            ':organization_account' => $organization_account,
            ':recipient_account' => $recipient_account,
            ':amount' => $amount,
            ':user_id' => $user_id,
            ':type' => $type,
            ':date' => $date,
            ':id' => $payment_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Fetch payment data
$stmt = $pdo->prepare("SELECT * FROM Payments WHERE id = :id");
$stmt->execute([':id' => $payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);
