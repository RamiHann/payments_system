<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Initialize variables for pagination and sorting
$columns = [
    'transactions' => ['id', 'user_id', 'amount', 'type', 'description', 'date'],
    'payments' => ['id', 'organization_account', 'recipient_account', 'amount', 'type', 'date', 'status'],
    'receipts' => ['id', 'transaction_id', 'user_id', 'amount', 'date']
];

$reportType = $_POST['report_type'] ?? '';
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';

// Validate report type
if (!array_key_exists($reportType, $columns)) {
    echo json_encode([
        'draw' => intval($_POST['draw']),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit();
}

// Validate date range
if ((!empty($startDate) && empty($endDate)) || (empty($startDate) && !empty($endDate))) {
    echo json_encode([
        'draw' => intval($_POST['draw']),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
    exit();
}

// Initialize pagination and sorting parameters
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
$orderDirection = $_POST['order'][0]['dir'] ?? 'asc';
$orderColumn = $columns[$reportType][$orderColumnIndex] ?? 'id';

// Prepare base query
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM " . ucfirst($reportType);
$conditions = [];
$params = [];

// Apply date range filter if both dates are provided
if (!empty($startDate) && !empty($endDate)) {
    $conditions[] = "date BETWEEN :startDate AND :endDate";
    $params[':startDate'] = $startDate;
    $params[':endDate'] = $endDate;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY $orderColumn $orderDirection LIMIT :start, :length";

// Prepare and execute the query
try {
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total record count without filters
    $totalQuery = "SELECT COUNT(*) FROM " . ucfirst($reportType);
    $totalStmt = $pdo->query($totalQuery);
    $totalRecords = $totalStmt->fetchColumn();

    // Get filtered record count
    $filteredQuery = "SELECT FOUND_ROWS()";
    $filteredStmt = $pdo->query($filteredQuery);
    $filteredRecords = $filteredStmt->fetchColumn();

    // Prepare data for DataTable
    $data = [];
    foreach ($results as $row) {
        $data[] = $row;
    }

    // Return JSON response
    echo json_encode([
        'draw' => intval($_POST['draw']),
        'recordsTotal' => intval($totalRecords),
        'recordsFiltered' => intval($filteredRecords),
        'data' => $data
    ]);
} catch (PDOException $e) {
    // Handle database error and return an error message
    echo json_encode([
        'draw' => intval($_POST['draw']),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?>
