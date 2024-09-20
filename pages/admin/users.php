<?php
require_once('../../config/config.php');
require_once('../../includes/auth.php');
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="/assets/css/dataTables/datatables.min.css">
    <link rel="stylesheet" href="/assets/toast/toast.min.css">
</head>

<body>
    <?php include_once '../../partials/navbar.php'; ?>

    <div class="main-content container">
        <div class="row mb-3">
            <div class="col-md-12">
                <h3>User Management</h3>
            </div>
        </div>

        <!-- Add User Form -->
        <div class="card mb-3">
            <div class="card-body">
                <h5>Add New User</h5>
                <form id="addUserForm" class="needs-validation" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                            <option value="supplier">Supplier</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <button type="button" id="create_btn" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>

        <!-- User List Table -->
        <div class="card">
            <div class="card-body">
                <h5>User List</h5>
                <table class="table table-striped" id="usersTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" class="needs-validation" method="POST" novalidate>
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <select class="form-control" id="editRole" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                                <option value="supplier">Supplier</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <button type="button" id="update_btn" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/dataTables/datatables.min.js"></script>
    <script src="/assets/js/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/toast/toast.min.js"></script>
    <script src="/assets/js/validations.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#usersTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '/includes/users.php',
                type: 'POST'
            },
            columns: [
                { data: 'id' },
                { data: 'username' },
                { data: 'role' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit_user_btn" data-id="${row.id}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete_user_btn" data-id="${row.id}">
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
                }
            }
        });

        // Add User Form Submission
        $('#create_btn').on('click', function(e) {
            e.preventDefault();
            var form = $("#addUserForm");
            if (form[0].checkValidity() === false) {
                form[0].classList.add("was-validated");
                return;
            };

            $.ajax({
                url: '/includes/create_user.php',
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $.toast({
                            heading: 'Success',
                            text: 'User added successfully.',
                            showHideTransition: 'slide',
                            icon: 'success',
                            position: 'top-right',
                        });
                        table.ajax.reload();
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: result.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                }
            });
        });

        // Edit User Button Click
        $(document).on('click', '.edit_user_btn', function() {
            var userId = $(this).data('id');
            $.ajax({
                url: '/includes/get_user.php',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#editUserId').val(result.data.id);
                        $('#editUsername').val(result.data.username);
                        $('#editRole').val(result.data.role);
                        $('#editUserModal').modal('show');
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: result.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                }
            });
        });

        // Edit User Form Submission
        $('#update_btn').on('click', function(e) {
            e.preventDefault();
            var form = $("#editUserForm");
            if (form[0].checkValidity() === false) {
                form[0].classList.add("was-validated");
                return;
            };
            $.ajax({
                url: '/includes/update_user.php',
                type: 'POST',
                data: form.serialize() + '&id=' + $('#editUserId').val(),
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $.toast({
                            heading: 'Success',
                            text: 'User updated successfully.',
                            showHideTransition: 'slide',
                            icon: 'success',
                            position: 'top-right',
                        });
                        table.ajax.reload();
                        $('#editUserModal').modal('hide');
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: result.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                }
            });
        });

        // Delete User Button Click
        $(document).on('click', '.delete_user_btn', function() {
            var userId = $(this).data('id');
            $('#confirmDeleteBtn').data('id', userId);
            $('#deleteUserModal').modal('show');
        });

        // Confirm Delete
        $('#confirmDeleteBtn').on('click', function() {
            var userId = $(this).data('id');
            $.ajax({
                url: '/includes/delete_user.php',
                type: 'POST',
                data: { id: userId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        $.toast({
                            heading: 'Success',
                            text: 'User deleted successfully.',
                            showHideTransition: 'slide',
                            icon: 'success',
                            position: 'top-right',
                        });
                        table.ajax.reload();
                        $('#deleteUserModal').modal('hide');
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: result.message,
                            showHideTransition: 'slide',
                            icon: 'error',
                            position: 'top-right',
                        });
                    }
                }
            });
        });
    });
    </script>
</body>

</html>
