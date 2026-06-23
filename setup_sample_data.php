<?php
// Setup sample data for testing
include('config/db.php');

echo "<h2>🔧 Setting up Sample Data</h2>";

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Clear existing timetable entries
$conn->query("DELETE FROM timetable");

// Add sample time slots if they don't exist
$result = $conn->query("SELECT COUNT(*) as count FROM timeslots");
$slot_count = $result->fetch_assoc()['count'];

if ($slot_count == 0) {
    echo "<p>Adding sample time slots...</p>";
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

// Add sample timetable entries
echo "<p>Adding sample timetable entries...</p>";

// Get IDs for sample data
$courses = $conn->query("SELECT course_id FROM courses LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$teachers = $conn->query("SELECT teacher_id FROM teachers LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$classrooms = $conn->query("SELECT room_id FROM classrooms LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$timeslots = $conn->query("SELECT slot_id FROM timeslots LIMIT 5")->fetch_all(MYSQLI_ASSOC);

if (count($courses) > 0 && count($teachers) > 0 && count($classrooms) > 0 && count($timeslots) > 0) {
    $sample_entries = [
        "({$courses[0]['course_id']}, {$teachers[0]['teacher_id']}, {$classrooms[0]['room_id']}, {$timeslots[0]['slot_id']})",
        "({$courses[1]['course_id']}, {$teachers[1]['teacher_id']}, {$classrooms[1]['room_id']}, {$timeslots[1]['slot_id']})",
        "({$courses[2]['course_id']}, {$teachers[2]['teacher_id']}, {$classrooms[2]['room_id']}, {$timeslots[2]['slot_id']})"
    ];
    
    foreach ($sample_entries as $entry) {
        $conn->query("INSERT INTO timetable (course_id, teacher_id, room_id, slot_id) VALUES $entry");
    }
    
    echo "<p style='color: green;'>✅ Sample timetable entries added</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Not enough sample data. Please add courses, teachers, and classrooms first.</p>";
}

echo "<br><a href='debug_timetable.php'>Check Timetable Data</a> | <a href='login.php'>Go to Login</a>";
?>
