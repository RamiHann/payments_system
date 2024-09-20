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

// Get the filters
$userFilter = isset($_POST['userFilter']) ? $_POST['userFilter'] : null;
$typeFilter = isset($_POST['typeFilter']) ? $_POST['typeFilter'] : null;
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;
$role = isset($_POST['role']) ? $_POST['role'] : null;
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

// Determine the order column from the DataTables request
$order_column_name = $columns[$order_column_index]['data'];

// Prepare the base SQL query
$query = "SELECT transactions.*, users.username FROM transactions JOIN users ON transactions.user_id = users.id WHERE 1=1";

// Admin can see all transactions, others can only see their own
if ($role !== 'admin') {
    $query .= " AND transactions.user_id = :user_id";
}

// Apply filters
if (!empty($userFilter)) {
    $query .= " AND transactions.user_id = :userFilter";
}

if (!empty($typeFilter)) {
    $query .= " AND transactions.type = :typeFilter";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND transactions.date BETWEEN :startDate AND :endDate";
}

if (!empty($search)) {
    $query .= " AND (transactions.description LIKE :search OR users.username LIKE :search)";
}

// Add ordering
$query .= " ORDER BY $order_column_name $order_dir LIMIT :start, :length";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameters
if ($role !== 'admin') {
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

if (!empty($userFilter)) {
    $stmt->bindValue(':userFilter', $userFilter, PDO::PARAM_INT);
}

if (!empty($typeFilter)) {
    $stmt->bindValue(':typeFilter', $typeFilter, PDO::PARAM_STR);
}

if (!empty($startDate) && !empty($endDate)) {
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
}

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions");
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();

// Prepare the JSON response for DataTables
$response = [
    'draw' => intval($draw),
    'recordsTotal' => $total_records,
    'recordsFiltered' => count($transactions),
    'data' => $transactions
];

// Send the JSON response
echo json_encode($response);
exit();
