<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// DataTables request data
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$search = $_POST['search']['value'];
$order_column_index = $_POST['order'][0]['column'];
$order_dir = $_POST['order'][0]['dir'];
$columns = $_POST['columns'];

// Get the user and their role
$user = json_decode($_SESSION['user'], true);
$role = $user['role'];
$user_id = $user['id'];

// Get the filters
$typeFilter = $_POST['typeFilter'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$statusFilter = $_POST['statusFilter'] ?? null;

// Determine the order column from the DataTables request
$order_column_name = $columns[$order_column_index]['data'];

// Prepare the base SQL query
$query = "SELECT * FROM Payments WHERE 1=1";

// Role-based restrictions
if ($role === 'employee' || $role === 'supplier') {
    // Employees and Suppliers can only see payments where they are the recipient
    $query .= " AND user_id = :user_id";
}

// Apply filters
if (!empty($typeFilter)) {
    $query .= " AND type = :typeFilter";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND date BETWEEN :startDate AND :endDate";
}

if (!empty($statusFilter)) {
    $query .= " AND status = :statusFilter";
}

if (!empty($search)) {
    $query .= " AND (organization_account LIKE :search OR recipient_account LIKE :search)";
}

// Add ordering
$query .= " ORDER BY $order_column_name $order_dir LIMIT :start, :length";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameters
if ($role === 'employee' || $role === 'supplier') {
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

if (!empty($typeFilter)) {
    $stmt->bindValue(':typeFilter', $typeFilter, PDO::PARAM_STR);
}

if (!empty($startDate) && !empty($endDate)) {
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
}

if (!empty($statusFilter)) {
    $stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
}

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records count for pagination
$count_query = "SELECT COUNT(*) FROM Payments WHERE 1=1";

// Apply role-based restrictions for total record count
if ($role === 'employee' || $role === 'supplier') {
    $count_query .= " AND user_id = :user_id";
}

// Apply filters for total count query
if (!empty($typeFilter)) {
    $count_query .= " AND type = :typeFilter";
}

if (!empty($startDate) && !empty($endDate)) {
    $count_query .= " AND date BETWEEN :startDate AND :endDate";
}

if (!empty($statusFilter)) {
    $count_query .= " AND status = :statusFilter";
}

if (!empty($search)) {
    $count_query .= " AND (organization_account LIKE :search OR recipient_account LIKE :search)";
}

$count_stmt = $pdo->prepare($count_query);

// Bind parameters for total count query
if ($role === 'employee' || $role === 'supplier') {
    $count_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

if (!empty($typeFilter)) {
    $count_stmt->bindValue(':typeFilter', $typeFilter, PDO::PARAM_STR);
}

if (!empty($startDate) && !empty($endDate)) {
    $count_stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $count_stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
}

if (!empty($statusFilter)) {
    $count_stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
}

if (!empty($search)) {
    $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();

// Prepare the JSON response for DataTables
$response = [
    'draw' => intval($draw),
    'recordsTotal' => $total_records,
    'recordsFiltered' => count($payments),
    'data' => $payments
];

// Send the JSON response
echo json_encode($response);
exit();
