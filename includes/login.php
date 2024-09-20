<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

$response = ['status' => 'error', 'message' => 'Error: Invalid request.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];

    // Prevent SQL injection by escaping user input
    $username = $pdo->quote($username);

    // Query to find the user by username
    $sql = "SELECT * FROM users WHERE username = $username";
    $result = $pdo->query($sql);

    if ($result && $result->rowCount() == 1) {
        $user = $result->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($_POST['password'], $user['password'])) {
            // Password is correct, user is authenticated
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user'] = json_encode($user);
            
            $response = [
                "status" => "success",
                "message" => "Login successful!"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Invalid username or password!"
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "User not found"
        ];
    }
}

echo json_encode($response);
exit();
?>
