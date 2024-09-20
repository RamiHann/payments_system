<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Fetch users for the user filter dropdown
$stmt = $pdo->prepare("SELECT id, username FROM Users ORDER BY username ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
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
                <h3>Receipts</h3>
            </div>
        </div>

        <!-- Add Create Receipt Button -->
        <div class="row mb-3">
            <div class="col-md-4">
                <a href="./create_receipt.php" class="btn btn-outline-primary">Create Receipt</a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-3">
            <!-- Filter by User -->
            <div class="col-md-4">
                <select class="form-control" id="userFilter" name="userFilter">
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Range Picker -->
            <div class="col-md-4">
                <input type="text" class="form-control" id="dateRangePicker" placeholder="Select Date Range">
                <input type="hidden" id="startDate">
                <input type="hidden" id="endDate">
            </div>

            <!-- Search Bar -->
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="Search...">
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="receiptsTable">
                    <thead>
                        <tr>
                            <th>Receipt ID</th>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Amount</th>
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

    <!-- View Receipt Modal -->
    <div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-labelledby="viewReceiptModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReceiptModalLabel">View Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Receipt details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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
                    <p>Are you sure you want to delete this receipt?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/toast/toast.min.js"></script>
    <script src="/assets/js/dataTables/datatables.min.js"></script>
    <script src="/assets/js/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#receiptsTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '/includes/receipts.php',
                type: 'POST',
                data: function(d) {
                    d.userFilter = $('#userFilter').val();
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                    d.search = $('#searchInput').val();
                }
            },
            columns: [
                { data: 'id' },
                { data: 'transaction_id' },
                { data: 'username' },
                { data: 'amount' },
                { data: 'date', render: function(data) {
                    return moment(data).format('YYYY.MM.DD HH:mm a');
                }},
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary view_receipt_btn" data-id="${row.id}">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a href="./update_receipt.php?id=${row.id}" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete_btn" data-id="${row.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            pagingType: 'full_numbers',
            language: {
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>',
                },
                lengthMenu: "Show _MENU_ records per page",
                zeroRecords: "No receipts found",
                info: "Showing page _PAGE_ of _PAGES_",
                infoEmpty: "No receipts available",
                infoFiltered: "(filtered from _MAX_ total receipts)"
            }
        });

        // Date Range Picker
        $('#dateRangePicker').daterangepicker({
            opens: 'left'
        }, function(start, end) {
            $('#startDate').val(start.format('YYYY-MM-DD'));
            $('#endDate').val(end.format('YYYY-MM-DD'));
            table.draw();
        });

        // Search event
        $('#searchInput').on('keyup', function() {
            table.draw();
        });

        // View Receipt Modal
        $(document).on('click', '.view_receipt_btn', function() {
            var receiptId = $(this).data('id');
            $.ajax({
                url: '/includes/view_receipt.php',
                type: 'POST',
                data: { id: receiptId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#viewReceiptModal .modal-body').html(result.html);
                        $('#viewReceiptModal').modal('show');
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

        // Handle Delete
        var deleteId;
        $(document).on('click', '.delete_btn', function() {
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: '/includes/delete_receipt.php',
                type: 'POST',
                data: { id: deleteId },
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
