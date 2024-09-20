<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// DataTables request data
$draw = $_POST['draw'] ?? 1;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$search = $_POST['search']['value'] ?? '';
$order_column_index = $_POST['order'][0]['column'] ?? 0;
$order_dir = $_POST['order'][0]['dir'] ?? 'asc';
$columns = $_POST['columns'] ?? [];

// Determine the order column from the DataTables request
$order_column_name = $columns[$order_column_index]['data'] ?? 'id';

// Prepare the base SQL query
$query = "SELECT * FROM users WHERE 1=1";

// Apply search
if (!empty($search)) {
    $query .= " AND (username LIKE :search OR role LIKE :search)";
}

// Add ordering
$query .= " ORDER BY $order_column_name $order_dir LIMIT :start, :length";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameters
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();

// Prepare the JSON response for DataTables
$response = [
    'draw' => intval($draw),
    'recordsTotal' => $total_records,
    'recordsFiltered' => count($users),
    'data' => $users
];

// Send the JSON response
echo json_encode($response);
exit();
?>
