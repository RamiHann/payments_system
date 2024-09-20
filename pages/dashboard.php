<?php
include_once '../config/config.php';
include_once '../includes/auth.php';
requireLogin();

// Fetch data depending on user role
$user = json_decode($_SESSION['user'], true);  // Decode JSON string to array
$role = $user['role'];
$userId = $user['id'];

// Fetch the 10 latest transactions from the database
$stmt = $pdo->prepare("SELECT date, description, amount, type FROM Transactions WHERE user_id = :user_id ORDER BY date DESC LIMIT 10");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Role-specific content as plain text
$roleSpecificContent = '';
if ($role === 'admin') {
    $roleSpecificContent = 'As an Admin, you can manage users and generate reports.';
} elseif ($role === 'employee') {
    $roleSpecificContent = 'As an Employee, you can view your salary payments and personal transactions.';
} elseif ($role === 'supplier') {
    $roleSpecificContent = 'As a Supplier, you can track the payments you have received.';
} elseif ($role === 'customer') {
    $roleSpecificContent = 'As a Customer, you can see your charges and receipts.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Bank System</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/dataTables/datatables.min.css">
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>Welcome, <?= htmlspecialchars($user['username']) ?>!</h3>
                <p>Your role: <strong><?= ucfirst(htmlspecialchars($role)) ?></strong></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- Quick Links -->
                <div class="row mt-4">
                    <?php if ($user['role'] !== 'supplier'): ?>
                    <div class="col-md-4">
                        <div class="card quick-link-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Transactions</h5>
                                <p class="card-text">View and manage all your transactions quickly and easily.</p>
                                <a href="/pages/transactions.php" class="btn btn-custom">View Transactions</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <div class="card quick-link-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Payments</h5>
                                <p class="card-text">Check all your payments, both received and made.</p>
                                <a href="/pages/payments.php" class="btn btn-custom">View Payments</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card quick-link-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Receipts</h5>
                                <p class="card-text">View all your receipts in one place for easy tracking.</p>
                                <a href="/pages/receipts.php" class="btn btn-custom">View Receipts</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary of Recent Transactions -->
                <?php if ($user['role'] !== 'supplier'): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4>Recent Transactions</h4>
                        <table id="recentTransactionsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                                    <td><?= htmlspecialchars($transaction['amount']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($transaction['type'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Role-specific Content -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h4>Role-specific Information</h4>
                        <p><?= $roleSpecificContent ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/dataTables/datatables.min.js"></script>
    <script src="/assets/js/dataTables/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTable for Recent Transactions
        $('#recentTransactionsTable').DataTable({
            pagingType: 'full_numbers',
            language: {
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>'
                }
            }
        });
    });
    </script>
</body>

</html>