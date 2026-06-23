<?php
$servername = "localhost";
$username = "root";   // XAMPP default
$password = "";       // XAMPP default
$dbname = "timetable_db"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
