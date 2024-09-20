<?php
require_once('../config/config.php');
require_once('../includes/auth.php');
requireLogin();

// Fetch payments data
$sql = "SELECT * FROM Payments";
$payments_result = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
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
                <h3>Payments</h3>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <a href="./create_payment.php" class="btn btn-outline-primary">Create Payment</a>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-3">
            <!-- Filter by Type -->
            <div class="col-md-4">
                <select class="form-control" id="typeFilter" name="typeFilter">
                    <option value="">-- Select Type --</option>
                    <option value="salary">Salary</option>
                    <option value="supplier">Supplier</option>
                    <option value="pension">Pension</option>
                </select>
            </div>

            <!-- Date Range Picker -->
            <div class="col-md-4">
                <input type="text" class="form-control" id="dateRangePicker" placeholder="Select Date Range">
                <input type="hidden" id="startDate">
                <input type="hidden" id="endDate">
            </div>

            <!-- Status Filter -->
            <div class="col-md-4">
                <select class="form-control" id="statusFilter" name="statusFilter">
                    <option value="">-- Select Status --</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="paymentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Organization Account</th>
                            <th>Recipient Account</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
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
                    <p>Are you sure you want to delete this payment?</p>
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
        var table = $('#paymentTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '/includes/payments.php',
                type: 'POST',
                data: function(d) {
                    d.typeFilter = $('#typeFilter').val();
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                    d.statusFilter = $('#statusFilter').val();
                }
            },
            columns: [
                { data: 'id' },
                { data: 'organization_account' },
                { data: 'recipient_account' },
                { data: 'amount' },
                { data: 'type' },
                { data: 'date', render: function(data) {
                    return moment(data).format('YYYY.MM.DD HH:mm a');
                }},
                { data: 'status' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <a href="./update_payment.php?id=${row.id}" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete_btn" data-id="${row.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            pagingType: 'full_numbers', // Pagination control options
            lengthMenu: [10, 25, 50, 100], // Page length options
            pageLength: 10, // Default page length
            language: {
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>',
                },
                lengthMenu: "Show _MENU_ records per page",
                zeroRecords: "No records found",
                info: "Showing page _PAGE_ of _PAGES_",
                infoEmpty: "No records available",
                infoFiltered: "(filtered from _MAX_ total records)"
            }
        });

        $('#dateRangePicker').daterangepicker({
            opens: 'left'
        }, function(start, end) {
            $('#startDate').val(start.format('YYYY-MM-DD'));
            $('#endDate').val(end.format('YYYY-MM-DD'));
            table.draw();
        });

        $('#typeFilter, #statusFilter').on('change', function() {
            table.draw();
        });

        var deleteId;
        $(document).on('click', '.delete_btn', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: '/includes/delete_payment.php',
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
