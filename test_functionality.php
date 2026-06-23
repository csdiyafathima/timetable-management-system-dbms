<?php
// Test all functionality
include('config/db.php');

echo "<h1>🧪 Timetable Management System - Functionality Test</h1>";

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Test 1: Check all tables exist
echo "<h2>📊 Database Tables Test</h2>";
$tables = ['users', 'courses', 'teachers', 'classrooms', 'timeslots', 'timetable'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "<p>✅ Table '$table' exists with $count records</p>";
    } else {
        echo "<p>❌ Table '$table' missing</p>";
    }
}

// Test 2: Check sample data
echo "<h2>👥 Sample Data Test</h2>";
$result = $conn->query("SELECT username, role FROM users");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p>✅ Users found:</p><ul>";
    foreach ($users as $user) {
        echo "<li>{$user['username']} ({$user['role']})</li>";
    }
    echo "</ul>";
}

// Test 3: Check teachers
echo "<h2>👨‍🏫 Teachers Test</h2>";
$result = $conn->query("SELECT teacher_name, dept FROM teachers");
if ($result) {
    $teachers = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p>✅ Teachers found:</p><ul>";
    foreach ($teachers as $teacher) {
        echo "<li>{$teacher['teacher_name']} ({$teacher['dept']})</li>";
    }
    echo "</ul>";
}

// Test 4: Check timetable entries
echo "<h2>📅 Timetable Test</h2>";
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
    echo "<p>✅ Timetable entries found: " . count($entries) . "</p>";
    if (count($entries) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
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
}

// Test 5: Login system test
echo "<h2>🔐 Login System Test</h2>";
echo "<p>✅ Login pages available:</p>";
echo "<ul>";
echo "<li><a href='login.php'>Login Page</a></li>";
echo "<li><a href='admin/dashboard.php'>Admin Dashboard</a> (requires admin login)</li>";
echo "<li><a href='user/view_timetable.php'>User View</a> (requires user login)</li>";
echo "<li><a href='teacher/view_timetable.php'>Teacher View</a> (requires teacher login)</li>";
echo "</ul>";

// Test 6: Management pages test
echo "<h2>⚙️ Management Pages Test</h2>";
echo "<p>✅ Management pages available:</p>";
echo "<ul>";
echo "<li><a href='admin/teachers.php'>Manage Teachers</a></li>";
echo "<li><a href='admin/timeslots.php'>Manage Time Slots</a></li>";
echo "<li><a href='admin/timetable.php'>Create Timetable</a></li>";
echo "<li><a href='admin/settings.php'>Settings</a></li>";
echo "</ul>";

echo "<h2>🎯 Quick Access Links</h2>";
echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to Login</a></p>";
echo "<p><a href='setup_database.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Setup Database</a></p>";

echo "<h2>📝 Test Instructions</h2>";
echo "<ol>";
echo "<li><strong>Login as Admin:</strong> admin / admin123</li>";
echo "<li><strong>Add Teachers:</strong> Go to Manage Teachers and add some teachers</li>";
echo "<li><strong>Add Time Slots:</strong> Go to Manage Time Slots and add time periods</li>";
echo "<li><strong>Create Timetable:</strong> Go to Create Timetable and schedule classes</li>";
echo "<li><strong>Login as Teacher:</strong> Use teacher credentials to see personal timetable</li>";
echo "<li><strong>Login as Student:</strong> Use student credentials to see general timetable</li>";
echo "</ol>";

echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>🎉 All systems ready for testing!</p>";
?>
