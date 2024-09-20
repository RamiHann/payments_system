<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

$payment_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $organization_account = $_POST['organization_account'];
    $recipient_account = $_POST['recipient_account'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $date = $_POST['date'];

    if (empty($organization_account) || empty($recipient_account) || empty($amount) || empty($type) || empty($date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Payments SET organization_account = :organization_account, recipient_account = :recipient_account, amount = :amount, type = :type, date = :date WHERE id = :id");
        $stmt->execute([
            ':organization_account' => $organization_account,
            ':recipient_account' => $recipient_account,
            ':amount' => $amount,
            ':type' => $type,
            ':date' => $date,
            ':id' => $payment_id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Fetch payment data
$stmt = $pdo->prepare("SELECT * FROM Payments WHERE id = :id");
$stmt->execute([':id' => $payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

$user = json_decode($_SESSION['user'], true);
$user_id = $user['id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Payment</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link href="/assets/css/datepicker3.css" rel="stylesheet">
    <style>
    .datepicker {
        z-index: 1050 !important;
        /* Ensure datepicker is on top of other elements */
    }

    .datepicker-dropdown {
        margin-top: 0px;
        /* Adjust margin to move the datepicker up */
    }

    .datepicker.datepicker-inline {
        margin: 0;
        /* Remove margin to align it better */
    }
    </style>
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>Update Payment</h3>
            </div>
        </div>
        <div class="card p-3">
            <div class="card-body">
                <form id="updatePaymentForm" method="POST" class="needs-validation mt-2" novalidate>
                    
                    <input type="hidden" name="id" value="<?= htmlspecialchars($payment['id']) ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="organization_account" class="form-label">Organization Account</label>
                                <input type="text" class="form-control" id="organization_account"
                                    name="organization_account"
                                    value="<?= htmlspecialchars($payment['organization_account']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="recipient_account" class="form-label">Recipient Account</label>
                                <input type="text" class="form-control" id="recipient_account" name="recipient_account"
                                    value="<?= htmlspecialchars($payment['recipient_account']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                                    value="<?= htmlspecialchars($payment['amount']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="salary" <?= $payment['type'] === 'salary' ? 'selected' : '' ?>>Salary
                                    </option>
                                    <option value="supplier" <?= $payment['type'] === 'supplier' ? 'selected' : '' ?>>
                                        Supplier
                                    </option>
                                    <option value="pension" <?= $payment['type'] === 'pension' ? 'selected' : '' ?>>
                                        Pension
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="text" class="form-control" id="date" name="date"
                                    value="<?= htmlspecialchars(date('Y-m-d', strtotime($payment['date']))) ?>"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="button" id="update_btn" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bootstrap-datepicker.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize datepicker
        $('#date').datepicker({
            format: 'yyyy-mm-dd',
            todayBtn: "linked",
            autoclose: true,
            todayHighlight: true,
            container: 'body' // Ensure datepicker is positioned relative to the body
        }).on('show', function(e) {
            // Adjust the position of the datepicker when shown
            var offset = $(this).offset();
            $('.datepicker').css({
                'top': (offset.top - $('.datepicker').outerHeight() - 10) +
                    'px', // Adjust top position
                'left': offset.left + 'px' // Align left position
            });
        });

        $('#update_btn').on('click', function(e) {
            e.preventDefault();
            var form = $("#updatePaymentForm");
            if (form[0].checkValidity() === false) {
                e.stopPropagation();
                form[0].classList.add("was-validated");
                return;
            }
            $.ajax({
                url: '/includes/update_payment.php',
                type: 'POST',
                data: form.serialize() + '&user_id=<?= $user_id ?>',
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        window.location.href = '/pages/payments.php';
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