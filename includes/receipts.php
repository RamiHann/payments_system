<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Get user info from the session
$user = json_decode($_SESSION['user'], true);
$userRole = $user['role'];
$userId = $user['id'];

// DataTables request data
$draw = $_POST['draw'] ?? 1;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$search = $_POST['search']['value'] ?? '';
$order_column_index = $_POST['order'][0]['column'] ?? 0;
$order_dir = $_POST['order'][0]['dir'] ?? 'asc';
$columns = $_POST['columns'] ?? [];

// Get the filters
$userFilter = $_POST['userFilter'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;

// Determine the order column from the DataTables request
$order_column_name = $columns[$order_column_index]['data'] ?? 'id';

// Prepare the base SQL query
$query = "SELECT receipts.*, users.username 
          FROM receipts 
          JOIN transactions ON receipts.transaction_id = transactions.id 
          JOIN users ON receipts.user_id = users.id 
          WHERE 1=1";

// Role-based filters
$params = [];

if ($userRole === 'admin') {
    // Admins can view all receipts
    if (!empty($userFilter)) {
        $query .= " AND users.username = :userFilter";
        $params[':userFilter'] = $userFilter;
    }
} elseif ($userRole === 'employee') {
    // Employees can view receipts related to their transactions
    $query .= " AND receipts.user_id = :userId";
    $params[':userId'] = $userId;
} elseif ($userRole === 'supplier') {
    // Suppliers should only see receipts related to their payments
    $query .= " AND receipts.user_id = :userId";
    $params[':userId'] = $userId;
} elseif ($userRole === 'customer') {
    // Customers can view their own receipts
    $query .= " AND receipts.user_id = :userId";
    $params[':userId'] = $userId;
}

// Apply date filters
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND receipts.date BETWEEN :startDate AND :endDate";
    $params[':startDate'] = $startDate;
    $params[':endDate'] = $endDate;
}

// Apply search filter
if (!empty($search)) {
    $query .= " AND (receipts.id LIKE :search OR transactions.id LIKE :search OR users.username LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add ordering
$query .= " ORDER BY $order_column_name $order_dir 
            LIMIT :start, :length";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

// Bind filter parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records count for pagination
$count_query = "SELECT COUNT(*) 
                FROM receipts 
                JOIN transactions ON receipts.transaction_id = transactions.id 
                JOIN users ON receipts.user_id = users.id 
                WHERE 1=1";

// Role-based filters for the count query
$count_params = [];

if ($userRole === 'admin') {
    if (!empty($userFilter)) {
        $count_query .= " AND users.username = :userFilter";
        $count_params[':userFilter'] = $userFilter;
    }
} elseif ($userRole === 'employee' || $userRole === 'supplier' || $userRole === 'customer') {
    $count_query .= " AND receipts.user_id = :userId";
    $count_params[':userId'] = $userId;
}

// Apply date filters to count query
if (!empty($startDate) && !empty($endDate)) {
    $count_query .= " AND receipts.date BETWEEN :startDate AND :endDate";
    $count_params[':startDate'] = $startDate;
    $count_params[':endDate'] = $endDate;
}

// Apply search filter to count query
if (!empty($search)) {
    $count_query .= " AND (receipts.id LIKE :search OR transactions.id LIKE :search OR users.username LIKE :search)";
    $count_params[':search'] = "%$search%";
}

$count_stmt = $pdo->prepare($count_query);

// Bind count parameters
foreach ($count_params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}

$count_stmt->execute();
$total_filtered_records = $count_stmt->fetchColumn();

// Prepare the JSON response for DataTables
$response = [
    'draw' => intval($draw),
    'recordsTotal' => $total_filtered_records,
    'recordsFiltered' => $total_filtered_records,
    'data' => $receipts
];

// Send the JSON response
echo json_encode($response);
exit();
?>
