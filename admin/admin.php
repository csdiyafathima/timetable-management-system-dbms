<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
</head>
<body>
  <h2>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</h2>
  <ul>
    <li><a href="teachers.php">Manage Teachers</a></li>
    <li><a href="timeslots.php">Manage Timeslots</a></li>
    <li><a href="timetable.php">Create Timetable</a></li>
    <li><a href="settings.php">Timetable Settings</a></li>
    <li><a href="view_timetable.php">View Timetable</a></li>
    <li><a href="../logout.php">Logout</a></li>
  </ul>
</body>
</html>
