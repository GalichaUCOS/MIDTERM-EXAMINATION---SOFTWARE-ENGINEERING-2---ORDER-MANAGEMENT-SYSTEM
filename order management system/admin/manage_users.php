<?php
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SuperAdmin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $action = $_GET['action'];
    $new_status = ($action == 'suspend') ? 1 : 0; 

    if ($user_id !== $_SESSION['user_id']) {
        $sql = "UPDATE users SET is_suspended = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_status, $user_id);
        $stmt->execute();
        
        header('Location: manage_users.php?message=' . urlencode("User status updated successfully."));
        exit();
    } else {
         header('Location: manage_users.php?error=' . urlencode("Cannot suspend your own active account."));
         exit();
    }
}


$users_result = $conn->query("SELECT user_id, username, role, is_suspended, date_added FROM users ORDER BY role, username");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage User Accounts</title>
        <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .suspended { background-color: #fdd; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <h2>Manage User Accounts (SuperAdmin)</h2>
    
    <?php 
    if (isset($_GET['message'])) echo "<p style='color:green;'>" . htmlspecialchars($_GET['message']) . "</p>";
    if (isset($_GET['error'])) echo "<p style='color:red;'>" . htmlspecialchars($_GET['error']) . "</p>";
    ?>

    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr class="<?= $user['is_suspended'] ? 'suspended' : ''; ?>">
                <td><?= htmlspecialchars($user['username']); ?></td>
                <td><?= htmlspecialchars($user['role']); ?></td>
                <td><?= $user['is_suspended'] ? 'SUSPENDED' : 'Active'; ?></td>
                <td><?= htmlspecialchars($user['date_added']); ?></td>
                <td>
                    <?php if ($user['user_id'] !== $_SESSION['user_id']): // Cannot suspend self ?>
                        <?php if ($user['is_suspended']): ?>
                            <a href="manage_users.php?action=activate&user_id=<?= $user['user_id']; ?>" style="color: green;">ACTIVATE</a>
                        <?php else: ?>
                            <a href="manage_users.php?action=suspend&user_id=<?= $user['user_id']; ?>" style="color: red;" onclick="return confirm('Are you sure you want to suspend this account?');">SUSPEND</a>
                        <?php endif; ?>
                    <?php else: ?>
                        (Current User)
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><a href="index.php">Go back to Dashboard</a></p>
</body>
</html>