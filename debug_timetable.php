<?php
// Debug timetable data
include('config/db.php');

echo "<h2>🔍 Timetable Debug Information</h2>";

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Check if timetable table has data
$result = $conn->query("SELECT COUNT(*) as count FROM timetable");
$count = $result->fetch_assoc()['count'];
echo "<p>📊 Total timetable entries: $count</p>";

if ($count > 0) {
    // Show all timetable entries
    $result = $conn->query("
        SELECT tt.*, c.course_name, t.teacher_name, cl.room_number, ts.day, ts.start_time, ts.end_time 
        FROM timetable tt 
        JOIN courses c ON tt.course_id = c.course_id 
        JOIN teachers t ON tt.teacher_id = t.teacher_id 
        JOIN classrooms cl ON tt.room_id = cl.room_id 
        JOIN timeslots ts ON tt.slot_id = ts.slot_id 
        ORDER BY ts.day, ts.start_time
    ");
    
    if ($result) {
        $entries = $result->fetch_all(MYSQLI_ASSOC);
        echo "<h3>📅 Current Timetable Entries:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Day</th><th>Time</th><th>Course</th><th>Teacher</th><th>Room</th></tr>";
        foreach ($entries as $entry) {
            echo "<tr>";
            echo "<td>{$entry['day']}</td>";
            echo "<td>" . date('H:i', strtotime($entry['start_time'])) . " - " . date('H:i', strtotime($entry['end_time'])) . "</td>";
            echo "<td>{$entry['course_name']}</td>";
            echo "<td>{$entry['teacher_name']}</td>";
            echo "<td>{$entry['room_number']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No timetable entries found. Add some entries first!</p>";
}

// Check if we have the required data
echo "<h3>📋 Required Data Check:</h3>";

$tables = ['courses', 'teachers', 'classrooms', 'timeslots'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    echo "<p>📊 $table: $count records</p>";
}

echo "<br><a href='login.php'>Go to Login</a> | <a href='admin/timetable.php'>Create Timetable</a>";
?>
