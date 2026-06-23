<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<h1>Manage Users Page</h1>
<p>Here you will manage users (Add/Edit/Delete)</p>
<a href="admin.php">Back to Dashboard</a>
