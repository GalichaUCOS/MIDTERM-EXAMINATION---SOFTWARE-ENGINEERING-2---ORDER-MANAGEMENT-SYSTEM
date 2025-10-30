<?php
session_start();
include('../config/db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (empty($data) || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'No items in the order or invalid data format.']);
    exit();
}

$total_amount = $data['total_amount'] ?? 0;
$cash_tendered = $data['cash_tendered'] ?? 0;
$change_given = $data['change_given'] ?? 0;
$items = $data['items'];

if ($cash_tendered < $total_amount || $total_amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid total or cash amount.']);
    exit();
}


$conn->begin_transaction();
$order_id = null;

try {
    $sql_order = "INSERT INTO orders (total_amount, cash_tendered, change_given, processed_by_user_id) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("dddi", $total_amount, $cash_tendered, $change_given, $user_id);
    
    if (!$stmt_order->execute()) {
        throw new Exception("Order insertion failed: " . $stmt_order->error);
    }
    
    $order_id = $conn->insert_id;
    $stmt_order->close();
    
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, item_price_at_time_of_order) VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);
    
    foreach ($items as $product_id => $item) {
        $product_id = (int)$product_id;
        $quantity = (int)$item['qty'];
        $item_price = (float)$item['price'];

        $stmt_item->bind_param("iidi", $order_id, $product_id, $quantity, $item_price);
        if (!$stmt_item->execute()) {
            throw new Exception("Order item insertion failed for product ID {$product_id}: " . $stmt_item->error);
        }
    }
    $stmt_item->close();


    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id, 'change_given' => number_format($change_given, 2)]);

} catch (Exception $e) {

    $conn->rollback();
    error_log("Transaction Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database transaction failed. Details: ' . $e->getMessage()]);
}

$conn->close();
?>