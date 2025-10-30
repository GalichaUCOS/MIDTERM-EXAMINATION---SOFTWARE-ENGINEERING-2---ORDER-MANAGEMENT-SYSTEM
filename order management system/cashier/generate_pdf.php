<?php
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    die("Access Denied.");
}

require('../fpdf/fpdf.php');

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
$transactions = $transactions_result->fetch_all(MYSQLI_ASSOC);

$sql_sum = "SELECT SUM(total_amount) AS grand_total FROM orders WHERE DATE(date_added) BETWEEN ? AND ?";
$stmt_sum = $conn->prepare($sql_sum);
$stmt_sum->bind_param("ss", $start_date, $end_date);
$stmt_sum->execute();
$grand_total = $stmt_sum->get_result()->fetch_assoc()['grand_total'] ?? 0;

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Order Transaction Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255); // Light blue background
$pdf->Cell(20, 7, 'ID', 1, 0, 'C', true);
$pdf->Cell(45, 7, 'Date & Time', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Cashier', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Total Amount (PHP)', 1, 1, 'C', true);

// Table Rows
$pdf->SetFont('Arial', '', 10);
foreach ($transactions as $row) {
    // ID (Order ID)
    $pdf->Cell(20, 6, $row['order_id'], 1, 0);
    // Date & Time
    $pdf->Cell(45, 6, date('Y-m-d H:i:s', strtotime($row['date_added'])), 1, 0);
    // Cashier Name
    $pdf->Cell(50, 6, $row['cashier_name'], 1, 0);
    // Total Amount
    $pdf->Cell(40, 6, number_format($row['total_amount'], 2), 1, 1, 'R');
}

// Grand Total Footer
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(115, 7, 'GRAND TOTAL SUM:', 1, 0, 'R', true);
$pdf->Cell(40, 7, number_format($grand_total, 2) . ' PHP', 1, 1, 'R', true);

// Output the PDF
$pdf->Output('I', 'Transaction_Report_' . $start_date . '_to_' . $end_date . '.pdf');

$conn->close();
?>