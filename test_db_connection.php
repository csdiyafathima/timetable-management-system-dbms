<?php
// Test database connection
include('config/db.php');

echo "<h2>Database Connection Test</h2>";

if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Users in database: $count</p>";
    }
    
    // Test teachers table
    $result = $conn->query("SELECT COUNT(*) as count FROM teachers");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Teachers in database: $count</p>";
    }
    
    // Test courses table
    $result = $conn->query("SELECT COUNT(*) as count FROM courses");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Courses in database: $count</p>";
    }
    
    // Test timeslots table
    $result = $conn->query("SELECT COUNT(*) as count FROM timeslots");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Time slots in database: $count</p>";
    }
    
    // Test classrooms table
    $result = $conn->query("SELECT COUNT(*) as count FROM classrooms");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Classrooms in database: $count</p>";
    }
    
    // Test timetable table
    $result = $conn->query("SELECT COUNT(*) as count FROM timetable");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Timetable entries in database: $count</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
}

echo "<br><a href='login.php'>Go to Login</a>";
?>
