<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Check user role and redirect or show toast if not permitted
$user = json_decode($_SESSION['user'], true);
$allowedRoles = ['admin', 'employee', 'customer', 'supplier']; // Allowed roles for accessing the transactions page

// Fetch users for the user filter dropdown (admin only)
$users_result = null;

if ($user['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT id, username FROM Users ORDER BY username ASC");
    $stmt->execute();
    $users_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!in_array($user['role'], $allowedRoles)) {
    echo "<script>
        $(document).ready(function() {
            $.toast({
                heading: 'Access Denied',
                text: 'You do not have permission to access this page.',
                showHideTransition: 'slide',
                icon: 'error',
                position: 'top-right'
            });
        });
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="/assets/css/dataTables/datatables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>Transactions</h3>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <a href="./create_transaction.php" class="btn btn-outline-primary">Create Transaction</a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-3">
            <!-- Filter by User (admin only) -->
            <?php if ($user['role'] === 'admin'): ?>
            <div class="col-md-4">
                <select class="form-control" id="userFilter" name="userFilter">
                    <option value="">-- Select User --</option>
                    <?php
                        if ($users_result) {
                            foreach ($users_result as $userOption) {
                                echo "<option value='" . htmlspecialchars($userOption['id']) . "'>" . htmlspecialchars($userOption['username']) . "</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Filter by Type -->
            <div class="col-md-4">
                <select class="form-control" id="typeFilter" name="typeFilter">
                    <option value="">-- Select Type --</option>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </div>

            <!-- Date Range Picker -->
            <div class="col-md-4">
                <input type="text" class="form-control" id="dateRangePicker" placeholder="Select Date Range">
                <input type="hidden" id="startDate">
                <input type="hidden" id="endDate">
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="transactionTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this tbody via Ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this transaction?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/toast/toast.min.js"></script>
    <script src="/assets/js/dataTables/datatables.min.js"></script>
    <script src="/assets/js/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    $(document).ready(function() {
        var table = $('#transactionTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '/includes/transactions.php', // URL to the server-side script
                type: 'POST',
                data: function(d) {
                    d.user_id = "<?= $user['id'] ?>"; // Current user id for filtering (non-admin)
                    d.role = "<?= $user['role'] ?>"; // Current user role for admin logic
                    d.userFilter = $('#userFilter').val(); // Filter by user
                    d.typeFilter = $('#typeFilter').val(); // Filter by type (debit/credit)
                    d.start_date = $('#startDate').val(); // Start date
                    d.end_date = $('#endDate').val(); // End date
                }
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'username'
                }, // Assuming you're fetching the username in the query
                {
                    data: 'amount'
                },
                {
                    data: 'type'
                },
                {
                    data: 'description'
                },
                {
                    data: 'date',
                    render: function(data, type, row) {
                        // Format the date using Moment.js
                        return moment(data).format('YYYY.MM.DD HH:mm A');
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <a href="./update_transaction.php?id=${row.id}" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete_btn" data-id="${row.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            pagingType: 'full_numbers', // Add pagination controls
            language: {
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>',
                }
            }
        });

        // Date range filter
        $('#dateRangePicker').daterangepicker({
            opens: 'left'
        }, function(start, end) {
            $('#startDate').val(start.format('YYYY-MM-DD'));
            $('#endDate').val(end.format('YYYY-MM-DD'));
            table.draw();
        });

        // Trigger table reload on filter change
        $('#userFilter, #typeFilter').on('change', function() {
            table.draw();
        });

        // Handle delete button click
        var deleteId;
        $(document).on('click', '.delete_btn', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: '/includes/delete_transaction.php',
                type: 'POST',
                data: {
                    id: deleteId
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#deleteModal').modal('hide');
                        table.draw();
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: result.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                },
                error: function() {
                    $.toast({
                        heading: 'Error',
                        text: 'An error occurred while processing your request. Please try again later.',
                        showHideTransition: 'slide',
                        icon: 'error',
                        position: 'top-right',
                    });
                }
            });
        });
    });
    </script>
</body>

</html>