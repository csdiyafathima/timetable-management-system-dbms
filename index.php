<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Redirect based on user role
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} else {
    header("Location: user/view_timetable.php");
    exit();
}
?>
