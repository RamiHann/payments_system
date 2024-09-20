<?php
require_once('../config/config.php');

// Prepare the SQL query to fetch users
$query = "SELECT id, username FROM users ORDER BY username";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate options for the select dropdown
$options = '<option value="">-- Select User --</option>';
foreach ($users as $user) {
    $options .= '<option value="' . htmlspecialchars($user['username']) . '">' . htmlspecialchars($user['username']) . '</option>';
}

// Return the options as JSON
echo json_encode(['options' => $options]);
exit();
?>