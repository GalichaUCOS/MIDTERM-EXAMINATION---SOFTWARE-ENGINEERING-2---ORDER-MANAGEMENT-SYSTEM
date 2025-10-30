<?php

?>
<header>
    <nav>
        <div class="logo">
            <span>Order Management System</span>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['role'])): ?>
                <span class="user-info">
                    Logged in as <strong><?= htmlspecialchars($_SESSION['username']); ?></strong> (<?= htmlspecialchars($_SESSION['role']); ?>)
                </span>

                <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
                    <a href="../admin/index.php">SA Dashboard</a>
                <?php endif; ?>

                <a href="../cashier/index.php">POS/Cashier</a>
                <a href="../cashier/view_reports.php">Reports</a>
                <a href="../logout.php" class="logout">Logout</a>
            <?php else: ?>
                <a href="../login.php" class="login">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
