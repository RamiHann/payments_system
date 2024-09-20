<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Fetch users and transactions for the form
$stmtUsers = $pdo->prepare("SELECT id, username FROM Users ORDER BY username ASC");
$stmtUsers->execute();
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

$stmtTransactions = $pdo->prepare("SELECT id FROM Transactions ORDER BY id DESC");
$stmtTransactions->execute();
$transactions = $stmtTransactions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Receipt</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>Create Receipt</h3>
            </div>
        </div>
        <div class="card p-3">
            <div class="card-body">
                <form id="createReceiptForm" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="transaction_id" class="form-label">Transaction</label>
                            <select class="form-control" id="transaction_id" name="transaction_id" required>
                                <option value="">-- Select Transaction --</option>
                                <?php foreach ($transactions as $transaction): ?>
                                    <option value="<?= htmlspecialchars($transaction['id']) ?>"><?= htmlspecialchars($transaction['id']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">-- Select User --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>
                    </div>

                    <button type="button" id="create_btn" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#create_btn').on('click', function(e) {
            e.preventDefault();
            var form = $("#createReceiptForm");
            if (form[0].checkValidity() === false) {
                e.stopPropagation();
                form.addClass("was-validated");
                return;
            }

            $.ajax({
                url: '/includes/create_receipt.php',
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        window.location.href = '/pages/receipts.php';
                    } else {
                        alert(result.message);
                    }
                }
            });
        });
    });
    </script>
</body>

</html>
