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

$message = '';
$message_type = '';

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = '✅ Timetable entry added successfully! The schedule has been updated.';
    $message_type = 'success';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $course_id = $_POST['course_id'];
            $teacher_id = $_POST['teacher_id'];
            $room_id = $_POST['room_id'];
            $slot_id = $_POST['slot_id'];
            
            if (empty($course_id) || empty($teacher_id) || empty($room_id) || empty($slot_id)) {
                $message = 'Please fill in all fields.';
                $message_type = 'error';
            } else {
                // Check for conflicts
                $stmt = $conn->prepare("SELECT COUNT(*) FROM timetable WHERE (teacher_id = ? OR room_id = ?) AND slot_id = ?");
                $stmt->execute([$teacher_id, $room_id, $slot_id]);
                $result = $stmt->get_result();
                $conflict_count = $result->fetch_row()[0];
                
                if ($conflict_count > 0) {
                    $message = 'Conflict detected! Teacher or room is already scheduled for this time slot.';
                    $message_type = 'warning';
                } else {
                    $stmt = $conn->prepare("INSERT INTO timetable (course_id, teacher_id, room_id, slot_id) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$course_id, $teacher_id, $room_id, $slot_id])) {
                        $message = '✅ Timetable entry added successfully! The schedule has been updated.';
                        $message_type = 'success';
                        // Redirect to prevent form resubmission
                        header("Location: timetable.php?success=1");
                        exit();
                    } else {
                        $message = '❌ Error adding timetable entry: ' . $conn->error;
                        $message_type = 'error';
                    }
                }
            }
        }
        
        elseif ($action == 'delete') {
            $tt_id = $_POST['tt_id'];
            $stmt = $conn->prepare("DELETE FROM timetable WHERE tt_id = ?");
            if ($stmt->execute([$tt_id])) {
                $message = 'Timetable entry deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error deleting timetable entry.';
                $message_type = 'error';
            }
        }
    }
}

// Get all data for dropdowns
$courses = [];
$teachers = [];
$classrooms = [];
$timeslots = [];
$timetable_entries = [];

// Get courses
$result = $conn->query("SELECT * FROM courses ORDER BY course_name");
if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
}

// Get teachers
$result = $conn->query("SELECT * FROM teachers ORDER BY teacher_name");
if ($result) {
    $teachers = $result->fetch_all(MYSQLI_ASSOC);
}

// Get classrooms
$result = $conn->query("SELECT * FROM classrooms ORDER BY room_number");
if ($result) {
    $classrooms = $result->fetch_all(MYSQLI_ASSOC);
}

// Get timeslots
$result = $conn->query("SELECT * FROM timeslots ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time");
if ($result) {
    $timeslots = $result->fetch_all(MYSQLI_ASSOC);
}

// Get timetable entries with details
$result = $conn->query("
    SELECT tt.*, c.course_name, t.teacher_name, cl.room_number, ts.day, ts.start_time, ts.end_time 
    FROM timetable tt 
    JOIN courses c ON tt.course_id = c.course_id 
    JOIN teachers t ON tt.teacher_id = t.teacher_id 
    JOIN classrooms cl ON tt.room_id = cl.room_id 
    JOIN timeslots ts ON tt.slot_id = ts.slot_id 
    ORDER BY FIELD(ts.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), ts.start_time
");
if ($result) {
    $timetable_entries = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Timetable - Timetable Management System</title>
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

        .header h1 {
            font-size: 1.8rem;
        }

        .header-info {
            text-align: right;
        }

        .date-time {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .logout-btn, .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 5px;
            display: inline-block;
            transition: background 0.3s ease;
            margin-left: 10px;
        }

        .logout-btn:hover, .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }

        .card-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th {
            background: #f8f9fa;
            color: #333;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 0 5px 5px 0;
        }

        .info-box h4 {
            color: #1976d2;
            margin-bottom: 0.5rem;
        }

        .info-box ul {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }

        .info-box li {
            margin: 0.25rem 0;
            color: #666;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .header-info {
                text-align: center;
                margin-top: 1rem;
            }

            .container {
                padding: 0 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 0.9rem;
            }

            .table th,
            .table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Create Timetable</h1>
        <div class="header-info">
            <div class="date-time">📅 <?php echo $currentDate; ?></div>
            <div class="date-time">🕐 <?php echo $currentTime; ?></div>
            <div>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</div>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Add New Timetable Entry</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-group">
                                <label for="course_id">Course:</label>
                                <select id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['course_id']; ?>">
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="teacher_id">Teacher:</label>
                                <select id="teacher_id" name="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['teacher_id']; ?>">
                                            <?php echo htmlspecialchars($teacher['teacher_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="room_id">Classroom:</label>
                                <select id="room_id" name="room_id" required>
                                    <option value="">Select Classroom</option>
                                    <?php foreach ($classrooms as $classroom): ?>
                                        <option value="<?php echo $classroom['room_id']; ?>">
                                            <?php echo htmlspecialchars($classroom['room_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="slot_id">Time Slot:</label>
                                <select id="slot_id" name="slot_id" required>
                                    <option value="">Select Time Slot</option>
                                    <?php foreach ($timeslots as $slot): ?>
                                        <option value="<?php echo $slot['slot_id']; ?>">
                                            <?php echo htmlspecialchars($slot['day']); ?> - 
                                            <?php echo date('H:i', strtotime($slot['start_time'])); ?> to 
                                            <?php echo date('H:i', strtotime($slot['end_time'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn">Add Timetable Entry</button>
                        </form>
                    </div>
                    
                    <div>
                        <div class="info-box">
                            <h4>📝 Instructions</h4>
                            <ul>
                                <li>Select a course from the dropdown</li>
                                <li>Choose the assigned teacher</li>
                                <li>Pick an available classroom</li>
                                <li>Select the time slot</li>
                                <li>System will check for conflicts automatically</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>📋 Current Timetable Entries</h2>
            </div>
            <div class="card-body">
                <?php if (empty($timetable_entries)): ?>
                    <div class="alert alert-info">
                        No timetable entries found. Add your first entry using the form above.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Teacher</th>
                                <th>Classroom</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timetable_entries as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['day']); ?></td>
                                    <td><?php echo date('H:i', strtotime($entry['start_time'])); ?> - <?php echo date('H:i', strtotime($entry['end_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($entry['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['room_number']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this timetable entry?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tt_id" value="<?php echo $entry['tt_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>