<?php
session_start();

if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'SuperAdmin') {
        header('Location: admin/index.php');
    } else {
        header('Location: cashier/index.php');
    }
    exit();
} else {
 
    header('Location: login.php');
    exit();
}
?>