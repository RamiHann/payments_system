<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>

<body>
    <?php include_once '../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>Reports</h3>
            </div>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <!-- Report Type -->
                        <div class="col-md-4">
                            <select class="form-control" id="reportType" name="report_type">
                                <option value="">-- Select Report Type --</option>
                                <option value="transactions">Transactions</option>
                                <option value="payments">Payments</option>
                                <option value="receipts">Receipts</option>
                            </select>
                        </div>

                        <!-- Date Range Picker -->
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="dateRangePicker" placeholder="Select Date Range">
                            <input type="hidden" id="startDate" name="start_date">
                            <input type="hidden" id="endDate" name="end_date">
                        </div>

                        <!-- Generate Button -->
                        <div class="col-md-4">
                            <input type="button" class="btn btn-primary" id="generate_report" value="Generate Report">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- List of Generated Reports -->
        <div class="card" id="transactionsTable" style="display: none;">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr id="transactionstableHeaders">
                            <!-- Dynamic headers based on report type -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this tbody via Ajax -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" id="paymentsTable" style="display: none;">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr id="paymentstableHeaders">
                            <!-- Dynamic headers based on report type -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this tbody via Ajax -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" id="receiptsTable" style="display: none;">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr id="receiptstableHeaders">
                            <!-- Dynamic headers based on report type -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this tbody via Ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/dataTables/datatables.min.js"></script>
    <script src="/assets/js/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    $(document).ready(function() {
        var table;

        // Initialize Date Range Picker
        $('#dateRangePicker').daterangepicker({
            opens: 'left'
        }, function(start, end) {
            $('#startDate').val(start.format('YYYY-MM-DD'));
            $('#endDate').val(end.format('YYYY-MM-DD'));
        });

        // Handle Generate Report button click
        $('#generate_report').on('click', function() {
            var reportType = $('#reportType').val();
            var columns = [];
            // Set up columns and headers based on the selected report type
            switch (reportType) {
                case 'transactions':
                    $('#transactionsTable').show();
                    $('#paymentsTable').hide();
                    $('#receiptsTable').hide();
                    $('#transactionstableHeaders').empty();
                    columns = [
                        { title: 'ID', data: 'id' },
                        { title: 'User ID', data: 'user_id' },
                        { title: 'Amount', data: 'amount' },
                        { title: 'Type', data: 'type' },
                        { title: 'Description', data: 'description' },
                        { title: 'Date', data: 'date' }
                    ];
                    columns.forEach(function(col) {
                        $('#transactionstableHeaders').append('<th>' + col.title + '</th>');
                    });
                    table = $('#transactionsTable table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/includes/reports.php',
                            type: 'POST',
                            data: function(d) {
                                d.report_type = $('#reportType').val();
                                d.start_date = $('#startDate').val();
                                d.end_date = $('#endDate').val();
                            },
                            dataSrc: function(json) {
                                if ($('#reportType').val() === '') {
                                    return []; // Clear data if no report type is selected
                                }
                                return json.data;
                            }
                        },
                        columns: columns,
                        pagingType: 'full_numbers',
                        language: {
                            paginate: {
                                previous: '<i class="fa fa-chevron-left"></i>',
                                next: '<i class="fa fa-chevron-right"></i>',
                            }
                        },
                        destroy: true, // Allow reinitialization of DataTable
                        order: [] // Ensure no default sorting
                    });
                    break;
                case 'payments':
                    $('#transactionsTable').hide();
                    $('#paymentsTable').show();
                    $('#receiptsTable').hide();
                    $('#paymentstableHeaders').empty();
                    columns = [
                        { title: 'ID', data: 'id' },
                        { title: 'Organization Account', data: 'organization_account' },
                        { title: 'Recipient Account', data: 'recipient_account' },
                        { title: 'Amount', data: 'amount' },
                        { title: 'Type', data: 'type' },
                        { title: 'Date', data: 'date' },
                        { title: 'Status', data: 'status' }
                    ];
                    columns.forEach(function(col) {
                        $('#paymentstableHeaders').append('<th>' + col.title + '</th>');
                    });
                    table = $('#paymentsTable table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/includes/reports.php',
                            type: 'POST',
                            data: function(d) {
                                d.report_type = $('#reportType').val();
                                d.start_date = $('#startDate').val();
                                d.end_date = $('#endDate').val();
                            },
                            dataSrc: function(json) {
                                if ($('#reportType').val() === '') {
                                    return []; // Clear data if no report type is selected
                                }
                                return json.data;
                            }
                        },
                        columns: columns,
                        pagingType: 'full_numbers',
                        language: {
                            paginate: {
                                previous: '<i class="fa fa-chevron-left"></i>',
                                next: '<i class="fa fa-chevron-right"></i>',
                            }
                        },
                        destroy: true, // Allow reinitialization of DataTable
                        order: [] // Ensure no default sorting
                    });
                    break;
                case 'receipts':
                    $('#transactionsTable').hide();
                    $('#paymentsTable').hide();
                    $('#receiptsTable').show();
                    $('#receiptstableHeaders').empty();
                    columns = [
                        { title: 'ID', data: 'id' },
                        { title: 'Transaction ID', data: 'transaction_id' },
                        { title: 'User ID', data: 'user_id' },
                        { title: 'Amount', data: 'amount' },
                        { title: 'Date', data: 'date' }
                    ];
                    columns.forEach(function(col) {
                        $('#receiptstableHeaders').append('<th>' + col.title + '</th>');
                    });
                    table = $('#receiptsTable table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/includes/reports.php',
                            type: 'POST',
                            data: function(d) {
                                d.report_type = $('#reportType').val();
                                d.start_date = $('#startDate').val();
                                d.end_date = $('#endDate').val();
                            },
                            dataSrc: function(json) {
                                if ($('#reportType').val() === '') {
                                    return []; // Clear data if no report type is selected
                                }
                                return json.data;
                            }
                        },
                        columns: columns,
                        pagingType: 'full_numbers',
                        language: {
                            paginate: {
                                previous: '<i class="fa fa-chevron-left"></i>',
                                next: '<i class="fa fa-chevron-right"></i>',
                            }
                        },
                        destroy: true, // Allow reinitialization of DataTable
                        order: [] // Ensure no default sorting
                    });
                    break;
                default:
                    return; // Do nothing if no valid report type selected
            }
        });
    });
    </script>
</body>

</html>
