<?php
session_start();
include('../config/db_connect.php');


if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'SuperAdmin')) {
    header('Location: ../login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $added_by_user_id = $_SESSION['user_id'];
    $image_path = NULL;

    if (empty($name) || $price <= 0) {
        $error = "Please provide a valid product name and price.";
    } else {
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $target_dir = "../assets/images/";

            $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
            $new_file_name = uniqid('prod_', true) . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_path = 'assets/images/' . $new_file_name; // Path stored in DB relative to root
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }


        if (!$error) {
            $sql = "INSERT INTO products (name, price, image_path, added_by_user_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsi", $name, $price, $image_path, $added_by_user_id);
            
            if ($stmt->execute()) {
                $message = "Product **" . htmlspecialchars($name) . "** added successfully!";
            } else {
                $error = "Failed to add product: " . $conn->error;
            }
        }
    }
}


$products_result = $conn->query("
    SELECT p.name, p.price, p.image_path, u.username, p.date_added 
    FROM products p 
    JOIN users u ON p.added_by_user_id = u.user_id 
    ORDER BY p.date_added DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Products - Order Management System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="form-container">
        <h2>Add New Product</h2>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="manage_products.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="price">Price (PHP):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="product_image">Product Image (optional):</label>
                <input type="file" id="product_image" name="product_image" accept="image/*">
            </div>

            <input type="submit" name="add_product" value="Add Product">
        </form>
    </div>

    <h2 style="text-align: center;">Existing Products</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Image</th>
                <th>Added By</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> PHP</td>
                        <td>
                            <?php if ($product['image_path']): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['username']); ?></td>
                        <td><?php echo htmlspecialchars($product['date_added']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
