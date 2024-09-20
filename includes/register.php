<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

$response = ['status' => 'error', 'message' => 'Error: Invalid request.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prevent SQL injection by escaping user input
    $username = $pdo->quote($username);

    // Check if the username already exists
    $sql = "SELECT * FROM users WHERE username = $username";
    $result = $pdo->query($sql);

    if ($result && $result->rowCount() > 0) {
        $response['message'] = "Username already exists!";
    } else {
        // Hash the password before saving
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            ':username' => $_POST['username'],
            ':password' => $hashed_password,
            ':role' => $_POST['role']
        ]);

        if ($stmt) {
            $response = [
                "status" => "success",
                "message" => "Registration successful! Please login."
            ];
        } else {
            $response['message'] = "Registration failed. Please try again.";
        }
    }
}

echo json_encode($response);
exit();
?>
