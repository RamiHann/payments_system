<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Handle report generation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';

    if (empty($reportType) || empty($startDate) || empty($endDate)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Validate the report type
    $validTypes = ['transactions', 'payments', 'receipts'];
    if (!in_array($reportType, $validTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid report type.']);
        exit();
    }

    // Prepare SQL query based on report type
    switch ($reportType) {
        case 'transactions':
            $query = "SELECT * FROM transactions WHERE date BETWEEN :startDate AND :endDate";
            break;
        case 'payments':
            $query = "SELECT * FROM payments WHERE date BETWEEN :startDate AND :endDate";
            break;
        case 'receipts':
            $query = "SELECT * FROM receipts WHERE date BETWEEN :startDate AND :endDate";
            break;
    }

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':startDate', $startDate);
    $stmt->bindValue(':endDate', $endDate);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit();
}
?>
