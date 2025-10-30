<?php
session_start();
include('../config/db_connect.php');


if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: ../login.php');
    exit();
}

$start_date = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01');
$end_date = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d');


$sql = "
    SELECT 
        o.order_id, 
        o.total_amount, 
        o.date_added,
        u.username AS cashier_name
    FROM orders o
    JOIN users u ON o.processed_by_user_id = u.user_id
    WHERE DATE(o.date_added) BETWEEN ? AND ?
    ORDER BY o.date_added DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$transactions_result = $stmt->get_result();

$sql_sum = "
    SELECT SUM(total_amount) AS grand_total
    FROM orders
    WHERE DATE(date_added) BETWEEN ? AND ?
";
$stmt_sum = $conn->prepare($sql_sum);
$stmt_sum->bind_param("ss", $start_date, $end_date);
$stmt_sum->execute();
$total_row = $stmt_sum->get_result()->fetch_assoc();
$grand_total = $total_row['grand_total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction Reports</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .total-row td { font-weight: bold; background-color: #f0f0f0; }
        .filter-form { margin-bottom: 20px; border: 1px solid #eee; padding: 15px; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <h2>History of Order Transactions (Reports Module)</h2>

    <div class="filter-form">
        <form action="view_reports.php" method="GET">
            <label for="date_start">Date Start:</label>
            <input type="date" id="date_start" name="date_start" value="<?= htmlspecialchars($start_date); ?>" required>
            
            <label for="date_end">Date End:</label>
            <input type="date" id="date_end" name="date_end" value="<?= htmlspecialchars($end_date); ?>" required>
            
            <button type="submit">Filter Transactions</button>
            <a href="generate_pdf.php?date_start=<?= urlencode($start_date); ?>&date_end=<?= urlencode($end_date); ?>" 
               target="_blank" style="margin-left: 20px;">
                Print PDF Report 📄
            </a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date & Time</th>
                <th>Processed By (Cashier)</th>
                <th>Total Amount (PHP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($transactions_result->num_rows > 0): ?>
                <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($transaction['order_id']); ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($transaction['date_added'])); ?></td>
                    <td><?= htmlspecialchars($transaction['cashier_name']); ?></td>
                    <td><?= number_format($transaction['total_amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">GRAND TOTAL SUM (<?= htmlspecialchars($start_date); ?> to <?= htmlspecialchars($end_date); ?>):</td>
                    <td><?= number_format($grand_total, 2); ?> PHP</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="4">No transactions found for the selected date range.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>