<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<h1>Timetable Settings Page</h1>
<p>Settings for timetable system go here.</p>
<a href="admin.php">Back to Dashboard</a>
