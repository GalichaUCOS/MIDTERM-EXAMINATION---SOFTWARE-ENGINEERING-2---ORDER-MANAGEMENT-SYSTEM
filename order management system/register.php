<?php
session_start();
include('config/db_connect.php');

$error = '';

$result = $conn->query("SELECT COUNT(*) AS count FROM users");
$row = $result->fetch_assoc();
$user_count = $row['count'];

if ($user_count > 0) {

    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Hash the password for security
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'SuperAdmin')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password_hash);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Super Administrator registered successfully. Please log in.";
            header('Location: login.php');
            exit();
        } else {
            $error = "Registration failed. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>SuperAdmin Setup - Order Management System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="form-container">
        <h2>Initial Super Administrator Setup</h2>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <input type="submit" value="Register SuperAdmin">
        </form>
    </div>
</body>
</html>
