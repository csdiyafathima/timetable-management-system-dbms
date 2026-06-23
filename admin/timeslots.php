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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $day = trim($_POST['day']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            
            if (empty($day) || empty($start_time) || empty($end_time)) {
                $message = 'Please fill in all fields.';
                $message_type = 'error';
            } elseif ($start_time >= $end_time) {
                $message = 'End time must be after start time.';
                $message_type = 'error';
            } else {
                // Check for overlapping time slots on the same day
                $stmt = $conn->prepare("SELECT COUNT(*) FROM timeslots WHERE day = ? AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))");
                $stmt->execute([$day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
                $overlap_count = $stmt->fetch_row()[0];
                
                if ($overlap_count > 0) {
                    $message = 'Time slot overlaps with existing slot on ' . $day . '.';
                    $message_type = 'warning';
                } else {
                    $stmt = $conn->prepare("INSERT INTO timeslots (day, start_time, end_time) VALUES (?, ?, ?)");
                    if ($stmt->execute([$day, $start_time, $end_time])) {
                        $message = 'Time slot added successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error adding time slot.';
                        $message_type = 'error';
                    }
                }
            }
        }
        
        elseif ($action == 'edit') {
            $slot_id = $_POST['slot_id'];
            $day = trim($_POST['day']);
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            
            if (empty($day) || empty($start_time) || empty($end_time)) {
                $message = 'Please fill in all fields.';
                $message_type = 'error';
            } elseif ($start_time >= $end_time) {
                $message = 'End time must be after start time.';
                $message_type = 'error';
            } else {
                // Check for overlapping time slots on the same day (excluding current slot)
                $stmt = $conn->prepare("SELECT COUNT(*) FROM timeslots WHERE day = ? AND slot_id != ? AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))");
                $stmt->execute([$day, $slot_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
                $overlap_count = $stmt->fetch_row()[0];
                
                if ($overlap_count > 0) {
                    $message = 'Time slot overlaps with existing slot on ' . $day . '.';
                    $message_type = 'warning';
                } else {
                    $stmt = $conn->prepare("UPDATE timeslots SET day = ?, start_time = ?, end_time = ? WHERE slot_id = ?");
                    if ($stmt->execute([$day, $start_time, $end_time, $slot_id])) {
                        $message = 'Time slot updated successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error updating time slot.';
                        $message_type = 'error';
                    }
                }
            }
        }
        
        elseif ($action == 'delete') {
            $slot_id = $_POST['slot_id'];
            
            // Check if timeslot has timetable entries
            $stmt = $conn->prepare("SELECT COUNT(*) FROM timetable WHERE slot_id = ?");
            $stmt->execute([$slot_id]);
            $timetable_count = $stmt->fetch_row()[0];
            
            if ($timetable_count > 0) {
                $message = 'Cannot delete time slot. It has scheduled classes. Please remove timetable entries first.';
                $message_type = 'warning';
            } else {
                $stmt = $conn->prepare("DELETE FROM timeslots WHERE slot_id = ?");
                if ($stmt->execute([$slot_id])) {
                    $message = 'Time slot deleted successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error deleting time slot.';
                    $message_type = 'error';
                }
            }
        }
    }
}

// Get all timeslots
$timeslots = [];
$result = $conn->query("SELECT t.*, COUNT(tt.tt_id) as timetable_count FROM timeslots t LEFT JOIN timetable tt ON t.slot_id = tt.slot_id GROUP BY t.slot_id ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time");
if ($result) {
    $timeslots = $result->fetch_all(MYSQLI_ASSOC);
}

// Get timeslot for editing
$edit_timeslot = null;
if (isset($_GET['edit'])) {
    $slot_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM timeslots WHERE slot_id = ?");
    $stmt->execute([$slot_id]);
    $result = $stmt->get_result();
    $edit_timeslot = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Time Slots - Timetable Management System</title>
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

        .btn-secondary {
            background: #6c757d;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
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

        .badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 0 5px 5px 0;
            margin-bottom: 1rem;
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

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 0 5px 5px 0;
        }

        .warning-box h4 {
            color: #856404;
            margin-bottom: 0.5rem;
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
        <h1>⏰ Manage Time Slots</h1>
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
                <h2><?php echo $edit_timeslot ? 'Edit Time Slot' : 'Add New Time Slot'; ?></h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_timeslot ? 'edit' : 'add'; ?>">
                            <?php if ($edit_timeslot): ?>
                                <input type="hidden" name="slot_id" value="<?php echo $edit_timeslot['slot_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="day">Day:</label>
                                <select id="day" name="day" required>
                                    <option value="">Select Day</option>
                                    <option value="Monday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
                                    <option value="Tuesday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                                    <option value="Wednesday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                                    <option value="Thursday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                                    <option value="Friday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
                                    <option value="Saturday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
                                    <option value="Sunday" <?php echo ($edit_timeslot && $edit_timeslot['day'] == 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="start_time">Start Time:</label>
                                <input type="time" id="start_time" name="start_time" 
                                       value="<?php echo $edit_timeslot ? $edit_timeslot['start_time'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_time">End Time:</label>
                                <input type="time" id="end_time" name="end_time" 
                                       value="<?php echo $edit_timeslot ? $edit_timeslot['end_time'] : ''; ?>" required>
                            </div>
                            
                            <button type="submit" class="btn">
                                <?php echo $edit_timeslot ? 'Update Time Slot' : 'Add Time Slot'; ?>
                            </button>
                            
                            <?php if ($edit_timeslot): ?>
                                <a href="timeslots.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div>
                        <div class="info-box">
                            <h4>📝 Instructions</h4>
                            <ul>
                                <li>Select the day of the week</li>
                                <li>Set start and end times</li>
                                <li>End time must be after start time</li>
                                <li>No overlapping time slots allowed</li>
                            </ul>
                        </div>
                        
                        <div class="warning-box">
                            <h4>⏰ Common Time Slots</h4>
                            <ul>
                                <li>Morning: 09:00 - 10:30</li>
                                <li>Late Morning: 10:45 - 12:15</li>
                                <li>Afternoon: 14:00 - 15:30</li>
                                <li>Late Afternoon: 15:45 - 17:15</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>📋 All Time Slots</h2>
            </div>
            <div class="card-body">
                <?php if (empty($timeslots)): ?>
                    <div class="alert alert-info">
                        No time slots found. Add your first time slot using the form above.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Scheduled Classes</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timeslots as $timeslot): ?>
                                <tr>
                                    <td><?php echo $timeslot['slot_id']; ?></td>
                                    <td><?php echo htmlspecialchars($timeslot['day']); ?></td>
                                    <td><?php echo date('H:i', strtotime($timeslot['start_time'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($timeslot['end_time'])); ?></td>
                                    <td>
                                        <?php 
                                        $start = strtotime($timeslot['start_time']);
                                        $end = strtotime($timeslot['end_time']);
                                        $duration = ($end - $start) / 60; // minutes
                                        echo $duration . ' min';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge"><?php echo $timeslot['timetable_count']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($timeslot['created_at'])); ?></td>
                                    <td>
                                        <a href="timeslots.php?edit=<?php echo $timeslot['slot_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <?php if ($timeslot['timetable_count'] == 0): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this time slot?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="slot_id" value="<?php echo $timeslot['slot_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-danger btn-sm" disabled title="Cannot delete - has scheduled classes">Delete</button>
                                        <?php endif; ?>
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