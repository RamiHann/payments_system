<?php
if (!isset($_SESSION)) {
    session_start();
}
$user = isset($_SESSION['user']) ? json_decode($_SESSION['user'], true) : null;
$currentFile = basename($_SERVER['PHP_SELF']);
?>

<header>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="/assets/images/logo.png" alt="Banking System" class="bank-header-logo" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <!-- Common Links for All Users -->
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'dashboard') !== false ? 'active' : ''; ?>" href="/pages/dashboard.php">Dashboard</a>
                        </li>
                        <?php if ($user['role'] !== 'supplier'): ?>
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'transaction') !== false ? 'active' : ''; ?>" href="/pages/transactions.php">Transactions</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'receipt') !== false ? 'active' : ''; ?>" href="/pages/receipts.php">Receipts</a>
                        </li>

                        <?php if ($user): ?>
                            <!-- Role-specific Links (Admin, Employee, Supplier, Customer) -->
                            <?php if ($user['role'] === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link m-2 <?php echo strpos($currentFile, 'user') !== false ? 'active' : ''; ?>" href="/pages/admin/users.php">User Management</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link m-2 <?php echo strpos($currentFile, 'report') !== false ? 'active' : ''; ?>" href="/pages/reports.php">Reports</a>
                                </li>
                            <?php elseif ($user['role'] === 'employee'): ?>
                                <li class="nav-item">
                                    <a class="nav-link m-2 <?php echo strpos($currentFile, 'payment') !== false ? 'active' : ''; ?>" href="/pages/payments.php">Salary Payments</a>
                                </li>
                            <?php elseif ($user['role'] === 'supplier'): ?>
                                <li class="nav-item">
                                    <a class="nav-link m-2 <?php echo strpos($currentFile, 'payment') !== false ? 'active' : ''; ?>" href="/pages/payments.php">Supplier Payments</a>
                                </li>
                            <?php elseif ($user['role'] === 'customer'): ?>
                                <li class="nav-item">
                                    <a class="nav-link m-2 <?php echo strpos($currentFile, 'payment') !== false ? 'active' : ''; ?>" href="/pages/payments.php">Charges</a>
                                </li>
                            <?php endif; ?>

                            <!-- Profile Link (Accessible by all roles) -->
                            <li class="nav-item">
                                <a class="nav-link m-2 <?php echo strpos($currentFile, 'profile') !== false ? 'active' : ''; ?>" href="/pages/profile.php">Profile</a>
                            </li>

                            <!-- Logout Link -->
                            <li class="nav-item">
                                <a class="nav-link m-2" href="/pages/logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <!-- If user is not logged in -->
                            <li class="nav-item">
                                <a class="nav-link m-2 <?php echo strpos($currentFile, 'login') !== false ? 'active' : ''; ?>" href="/pages/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
