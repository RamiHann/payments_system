<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Fetch the transaction details by its ID
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Transactions WHERE id = :id");
    $stmt->execute([':id' => $transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo "Transaction not found.";
        exit();
    }
} else {
    header('Location: /pages/transactions.php');
    exit();
}

// Check if the user is an admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch users for the admin dropdown
$users_sql = "SELECT id, username FROM users";
$users_result = $pdo->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Transaction | Bank System</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>Update Transaction</h3>
            </div>
        </div>
        <div class="card p-3">
            <div class="card-body">
                <form novalidate id="updateTransactionForm" method="POST" class="needs-validation mt-2">
                    <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($transaction['id']) ?>">
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <div class="row">
                        <?php if ($isAdmin): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id">User</label>
                                <select class="form-control" id="user_id" name="user_id" required>
                                    <option disabled value="">-- Select User --</option>
                                    <?php
                                    if ($users_result->rowCount() > 0) {
                                        while ($user = $users_result->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $transaction['user_id'] == $user['id'] ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($user['id']) . "' $selected>" . htmlspecialchars($user['username']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    User is required.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount">Amount</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?= htmlspecialchars($transaction['amount']) ?>" required>
                                <div class="invalid-feedback">
                                    Amount is required.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type">Type</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option disabled value="">-- Select Type --</option>
                                    <option value="debit" <?= $transaction['type'] == 'debit' ? 'selected' : '' ?>>Debit</option>
                                    <option value="credit" <?= $transaction['type'] == 'credit' ? 'selected' : '' ?>>Credit</option>
                                </select>
                                <div class="invalid-feedback">
                                    Type is required.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($transaction['description']) ?></textarea>
                                <div class="invalid-feedback">
                                    Description is required.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" id="update_btn" class="btn btn-primary">Update Transaction</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/validations.js"></script>
    <script>
    $("#updateTransactionForm").on("submit", function(e) {
        e.preventDefault();
        var form = $(this);
        if (form[0].checkValidity() === false) {
            e.stopPropagation();
            form[0].classList.add("was-validated");
            return;
        }

        $.ajax({
            url: '/includes/update_transaction.php',
            type: 'POST',
            data: form.serialize(),
            success: function(result) {
                const data = JSON.parse(result);
                if (data.status === "success") {
                    window.location.href = '/pages/transactions.php';
                } else {
                    $("#errorMsg").html(
                        `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>${data.message}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`
                    );
                }
            }
        });
    });
    </script>
</body>

</html>
