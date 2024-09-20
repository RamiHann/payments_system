<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = :id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit();
    }

    // Fetch the stored password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
        exit();
    }

    // Update the password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css">
</head>
<body>
    <?php include_once '../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>User Profile</h3>
            </div>
        </div>

        <!-- Display User Details -->
        <div class="card">
            <div class="card-body">
                <h5>Username: <?php echo htmlspecialchars($user['username']); ?></h5>
                <h5>Role: <?php echo htmlspecialchars($user['role']); ?></h5>
            </div>
        </div>

        <!-- Password Update Form -->
        <div class="card mt-3">
            <div class="card-body">
                <h5>Update Password</h5>
                <form id="updatePasswordForm" method="POST" class="needs-validation mt-2" novalidate>
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                    <button type="button" id="update_profile" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#update_profile').on('click', function(e) {
            e.preventDefault();
            var form = $("#updatePasswordForm");
            if (form[0].checkValidity() === false) {
                e.stopPropagation();
                form[0].classList.add("was-validated");
                return;
            }
            $.ajax({
                url: '../includes/profile.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    const result = JSON.parse(response);
                    $('#errorMsg').html(`<div class="alert alert-${result.status}">${result.message}</div>`);
                },
                error: function() {
                    $('#errorMsg').html('<div class="alert alert-danger">An error occurred. Please try again later.</div>');
                }
            });
        });
    });
    </script>
</body>
</html>
