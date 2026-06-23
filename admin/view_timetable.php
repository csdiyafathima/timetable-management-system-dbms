<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include('../config/db.php');

// Set timezone and get current date
date_default_timezone_set('Asia/Kolkata');
$currentDate = date("l, d F Y");
$currentTime = date("H:i:s");
$currentDay = date("l");

// Filters (teacher, course, classroom)
$teacherFilter = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$courseFilter  = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$roomFilter    = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

// Fetch options for filters
$teachersList = [];
$coursesList = [];
$classroomsList = [];
$r = $conn->query("SELECT teacher_id, teacher_name FROM teachers ORDER BY teacher_name");
if ($r) { $teachersList = $r->fetch_all(MYSQLI_ASSOC); }
$r = $conn->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
if ($r) { $coursesList = $r->fetch_all(MYSQLI_ASSOC); }
$r = $conn->query("SELECT room_id, room_number FROM classrooms ORDER BY room_number");
if ($r) { $classroomsList = $r->fetch_all(MYSQLI_ASSOC); }

// Build filtered query
$sql = "
    SELECT tt.*, c.course_name, t.teacher_name, cl.room_number, ts.day, ts.start_time, ts.end_time
    FROM timetable tt
    JOIN courses c ON tt.course_id = c.course_id
    JOIN teachers t ON tt.teacher_id = t.teacher_id
    JOIN classrooms cl ON tt.room_id = cl.room_id
    JOIN timeslots ts ON tt.slot_id = ts.slot_id
    WHERE 1=1";
if ($teacherFilter > 0) { $sql .= " AND tt.teacher_id = ".$teacherFilter; }
if ($courseFilter  > 0) { $sql .= " AND tt.course_id = ".$courseFilter; }
if ($roomFilter    > 0) { $sql .= " AND tt.room_id = ".$roomFilter; }
$sql .= " ORDER BY FIELD(ts.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ts.start_time";

// Get timetable data (filtered)
$timetable_data = [];
$result = $conn->query($sql);
if ($result) { $timetable_data = $result->fetch_all(MYSQLI_ASSOC); }

// Organize data by day and time slot
$organized_timetable = [];
foreach ($timetable_data as $entry) {
    $day = $entry['day'];
    $start_time = $entry['start_time'];
    $end_time = $entry['end_time'];
    // Create multiple possible keys for matching
    $time_slot_key1 = $start_time . '-' . $end_time;
    $time_slot_key2 = date('H:i:s', strtotime($start_time)) . '-' . date('H:i:s', strtotime($end_time));
    $organized_timetable[$day][$time_slot_key1] = $entry;
    $organized_timetable[$day][$time_slot_key2] = $entry;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetable - Timetable Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { font-size: 1.8rem; }
        .header-info { text-align: right; }
        .date-time { font-size: 0.9rem; opacity: 0.9; }
        .logout-btn, .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; margin-top: 5px; display: inline-block; transition: background 0.3s ease; margin-left: 10px; }
        .logout-btn:hover, .back-btn:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .filter-bar { background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 1rem 1.5rem; margin-bottom: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: end; }
        .filter-group label { display: block; margin-bottom: .4rem; color: #333; font-weight: 600; }
        .filter-group select { width: 100%; padding: 10px; border: 2px solid #e1e5e9; border-radius: 8px; }
        .btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-secondary { background: #6c757d; }
        .welcome-card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 2rem; text-align: center; }
        .welcome-card h2 { color: #333; margin-bottom: 0.5rem; }
        .welcome-card p { color: #666; font-size: 1.1rem; }
        .timetable-container { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
        .timetable-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; text-align: center; }
        .timetable-header h3 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .timetable-table { width: 100%; border-collapse: collapse; }
        .timetable-table th { background: #f8f9fa; color: #333; padding: 1rem; text-align: center; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        .timetable-table td { padding: 1rem; text-align: center; border-bottom: 1px solid #e9ecef; transition: background-color 0.3s ease; }
        .timetable-table tr:hover td { background-color: #f8f9fa; }
        .current-day { background-color: #e3f2fd !important; font-weight: 600; }
        .subject-cell { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; border-radius: 5px; padding: 0.5rem; margin: 0.2rem; display: inline-block; min-width: 80px; }
        .lab-cell { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; font-weight: 600; border-radius: 5px; padding: 0.5rem; margin: 0.2rem; display: inline-block; min-width: 80px; }
        .break-cell { background: #ffeaa7; color: #2d3436; font-weight: 600; border-radius: 5px; padding: 0.5rem; margin: 0.2rem; display: inline-block; min-width: 80px; }
        .no-class { color: #b2bec3; font-style: italic; }
        .today-highlight { background: linear-gradient(135deg, #00b894 0%, #00a085 100%); color: white; font-weight: bold; padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; margin-bottom: 0.5rem; }
        .stat-label { color: #666; font-size: 0.9rem; }
        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .header-info { text-align: center; margin-top: 1rem; }
            .container { padding: 0 1rem; }
            .timetable-table { font-size: 0.8rem; }
            .timetable-table th, .timetable-table td { padding: 0.5rem; }
            .subject-cell, .lab-cell, .break-cell { min-width: 60px; font-size: 0.7rem; padding: 0.3rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👁️ View Timetable</h1>
        <div class="header-info">
            <div class="date-time">📅 <?php echo $currentDate; ?></div>
            <div class="date-time">🕐 <?php echo $currentTime; ?></div>
            <div>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</div>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>📚 Complete Timetable Overview</h2>
            <p>View and manage all scheduled classes across the institution</p>
            <a href="view_timetable.php" class="btn" style="margin-top: 1rem; display: inline-block;">🔄 Refresh Timetable</a>
        </div>

        <form method="get" class="filter-bar">
            <div class="filter-group">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="0">All Teachers</option>
                    <?php foreach ($teachersList as $t): ?>
                        <option value="<?php echo $t['teacher_id']; ?>" <?php echo ($teacherFilter==$t['teacher_id'])?'selected':''; ?>><?php echo htmlspecialchars($t['teacher_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id">
                    <option value="0">All Courses</option>
                    <?php foreach ($coursesList as $c): ?>
                        <option value="<?php echo $c['course_id']; ?>" <?php echo ($courseFilter==$c['course_id'])?'selected':''; ?>><?php echo htmlspecialchars($c['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="room_id">Classroom</label>
                <select id="room_id" name="room_id">
                    <option value="0">All Classrooms</option>
                    <?php foreach ($classroomsList as $r): ?>
                        <option value="<?php echo $r['room_id']; ?>" <?php echo ($roomFilter==$r['room_id'])?'selected':''; ?>><?php echo htmlspecialchars($r['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group"><button type="submit" class="btn">🔎 Apply Filters</button></div>
            <div class="filter-group"><a href="view_timetable.php" class="btn btn-secondary">♻️ Reset</a></div>
        </form>

        <div class="timetable-container">
            <div class="timetable-header">
                <h3>📅 Weekly Schedule Overview</h3>
                <p>Current Day: <span class="today-highlight"><?php echo $currentDay; ?></span></p>
            </div>

            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>9:00 - 10:30</th>
                        <th>10:45 - 12:15</th>
                        <th>12:15 - 1:00</th>
                        <th>2:00 - 3:30</th>
                        <th>3:45 - 5:15</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $time_slots = [
                        '09:00:00-10:30:00' => '9:00 - 10:30',
                        '10:45:00-12:15:00' => '10:45 - 12:15',
                        '12:15:00-13:00:00' => '12:15 - 1:00',
                        '14:00:00-15:30:00' => '2:00 - 3:30',
                        '15:45:00-17:15:00' => '3:45 - 5:15'
                    ];
                    foreach ($days as $day): 
                        $is_current_day = ($currentDay == $day);
                    ?>
                        <tr <?php echo $is_current_day ? 'class="current-day"' : ''; ?>>
                            <td><strong><?php echo $day; ?></strong><?php echo $is_current_day ? ' <span class="today-highlight">TODAY</span>' : ''; ?></td>
                            <?php foreach ($time_slots as $slot_key => $slot_display): 
                                $entry = isset($organized_timetable[$day][$slot_key]) ? $organized_timetable[$day][$slot_key] : null;
                            ?>
                                <td>
                                    <?php if ($entry): ?>
                                        <div class="subject-cell">
                                            <strong><?php echo htmlspecialchars($entry['course_name']); ?></strong><br>
                                            <small>👨‍🏫 <?php echo htmlspecialchars($entry['teacher_name']); ?></small><br>
                                            <small>🏫 <?php echo htmlspecialchars($entry['room_number']); ?></small>
                                        </div>
                                    <?php elseif ($slot_display == '12:15 - 1:00'): ?>
                                        <span class="break-cell">Lunch</span>
                                    <?php elseif ($day == 'Saturday' && in_array($slot_display, ['2:00 - 3:30', '3:45 - 5:15'])): ?>
                                        <span class="no-class">Half Day</span>
                                    <?php else: ?>
                                        <span class="no-class">Free</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="welcome-card">
            <h3>📝 Legend</h3>
            <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; margin-top: 1rem;">
                <div><span class="subject-cell">Subject</span> - Regular Classes</div>
                <div><span class="lab-cell">Lab</span> - Laboratory Sessions</div>
                <div><span class="break-cell">Break</span> - Break Time</div>
            </div>
        </div>
    </div>
</body>
</html>