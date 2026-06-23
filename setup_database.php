<?php
// Setup database with sample data
include('config/db.php');

echo "<h2>Database Setup</h2>";

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Check if tables exist and create if needed
$tables = ['users', 'courses', 'teachers', 'classrooms', 'timeslots', 'timetable'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Table '$table' does not exist. Please run the schema.sql file first.</p>";
    } else {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
    }
}

// Insert sample data if tables are empty
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = $result->fetch_assoc()['count'];

if ($user_count == 0) {
    echo "<p>Inserting sample users...</p>";
    $conn->query("INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin'), ('student1', 'student123', 'user')");
    echo "<p style='color: green;'>✅ Sample users added</p>";
}

$result = $conn->query("SELECT COUNT(*) as count FROM timeslots");
$slot_count = $result->fetch_assoc()['count'];

if ($slot_count == 0) {
    echo "<p>Inserting sample time slots...</p>";
    $sample_slots = [
        "('Monday', '09:00:00', '10:30:00')",
        "('Monday', '10:45:00', '12:15:00')",
        "('Monday', '14:00:00', '15:30:00')",
        "('Tuesday', '09:00:00', '10:30:00')",
        "('Tuesday', '10:45:00', '12:15:00')",
        "('Tuesday', '14:00:00', '15:30:00')",
        "('Wednesday', '09:00:00', '10:30:00')",
        "('Wednesday', '14:00:00', '15:30:00')",
        "('Thursday', '09:00:00', '10:30:00')",
        "('Thursday', '10:45:00', '12:15:00')",
        "('Friday', '09:00:00', '10:30:00')"
    ];
    
    foreach ($sample_slots as $slot) {
        $conn->query("INSERT INTO timeslots (day, start_time, end_time) VALUES $slot");
    }
    echo "<p style='color: green;'>✅ Sample time slots added</p>";
}

echo "<br><a href='login.php'>Go to Login</a> | <a href='test_db_connection.php'>Test Database</a>";
?>
