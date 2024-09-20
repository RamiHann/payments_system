<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

$user = json_decode($_SESSION['user'], true);
$user_id = $user['id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Payment</title>
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
                <h3>Create Payment</h3>
            </div>
        </div>
        <div class="card p-3">
            <div class="card-body">
                <form novalidate id="createPaymentForm" method="POST" class="needs-validation mt-2">
                    <div id="errorMsg" class="form-group col-md-12"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="organization_account" class="form-label">Organization Account</label>
                                <input type="text" class="form-control" id="organization_account"
                                    name="organization_account" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="recipient_account" class="form-label">Recipient Account</label>
                                <input type="text" class="form-control" id="recipient_account" name="recipient_account"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="salary">Salary</option>
                                    <option value="supplier">Supplier</option>
                                    <option value="pension">Pension</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="text" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="create_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/validations.js"></script>
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

        $('#create_btn').on('click', function(e) {
            e.preventDefault();
            var form = $("#createPaymentForm");
            if (form[0].checkValidity() === false) {
                e.stopPropagation();
                form[0].classList.add("was-validated");
                return;
            }
            $.ajax({
                url: '/includes/create_payment.php',
                type: 'POST',
                data: form.serialize() + '&user_id=<?= $user_id ?>',
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        window.location.href = '/pages/payments.php';
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
    });
    </script>
</body>

</html>