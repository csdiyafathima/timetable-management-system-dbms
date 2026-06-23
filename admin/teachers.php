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
            $teacher_name = trim($_POST['teacher_name']);
            $dept = trim($_POST['dept']);
            
            if (empty($teacher_name) || empty($dept)) {
                $message = 'Please fill in all fields.';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("INSERT INTO teachers (teacher_name, dept) VALUES (?, ?)");
                if ($stmt->execute([$teacher_name, $dept])) {
                    $message = 'Teacher added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error adding teacher.';
                    $message_type = 'error';
                }
            }
        }
        
        elseif ($action == 'edit') {
            $teacher_id = $_POST['teacher_id'];
            $teacher_name = trim($_POST['teacher_name']);
            $dept = trim($_POST['dept']);
            
            if (empty($teacher_name) || empty($dept)) {
                $message = 'Please fill in all fields.';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE teachers SET teacher_name = ?, dept = ? WHERE teacher_id = ?");
                if ($stmt->execute([$teacher_name, $dept, $teacher_id])) {
                    $message = 'Teacher updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error updating teacher.';
                    $message_type = 'error';
                }
            }
        }
        
        elseif ($action == 'delete') {
            $teacher_id = $_POST['teacher_id'];
            
            // Check if teacher has timetable entries
            $stmt = $conn->prepare("SELECT COUNT(*) FROM timetable WHERE teacher_id = ?");
            $stmt->execute([$teacher_id]);
            $timetable_count = $stmt->fetch_row()[0];
            
            if ($timetable_count > 0) {
                $message = 'Cannot delete teacher. They have scheduled classes. Please remove timetable entries first.';
                $message_type = 'warning';
            } else {
                $stmt = $conn->prepare("DELETE FROM teachers WHERE teacher_id = ?");
                if ($stmt->execute([$teacher_id])) {
                    $message = 'Teacher deleted successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error deleting teacher.';
                    $message_type = 'error';
                }
            }
        }
    }
}

// Get all teachers
$teachers = [];
$result = $conn->query("SELECT t.*, COUNT(tt.tt_id) as timetable_count FROM teachers t LEFT JOIN timetable tt ON t.teacher_id = tt.teacher_id GROUP BY t.teacher_id ORDER BY t.teacher_name");
if ($result) {
    $teachers = $result->fetch_all(MYSQLI_ASSOC);
}

// Get teacher for editing
$edit_teacher = null;
if (isset($_GET['edit'])) {
    $teacher_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    $result = $stmt->get_result();
    $edit_teacher = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Timetable Management System</title>
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
        <h1>👨‍🏫 Manage Teachers</h1>
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
                <h2><?php echo $edit_teacher ? 'Edit Teacher' : 'Add New Teacher'; ?></h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_teacher ? 'edit' : 'add'; ?>">
                            <?php if ($edit_teacher): ?>
                                <input type="hidden" name="teacher_id" value="<?php echo $edit_teacher['teacher_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="teacher_name">Teacher Name:</label>
                                <input type="text" id="teacher_name" name="teacher_name" 
                                       value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['teacher_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="dept">Department:</label>
                                <select id="dept" name="dept" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Mathematics" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                    <option value="Physics" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                    <option value="Chemistry" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                                    <option value="Biology" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Biology') ? 'selected' : ''; ?>>Biology</option>
                                    <option value="English" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'English') ? 'selected' : ''; ?>>English</option>
                                    <option value="History" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'History') ? 'selected' : ''; ?>>History</option>
                                    <option value="Economics" <?php echo ($edit_teacher && $edit_teacher['dept'] == 'Economics') ? 'selected' : ''; ?>>Economics</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn">
                                <?php echo $edit_teacher ? 'Update Teacher' : 'Add Teacher'; ?>
                            </button>
                            
                            <?php if ($edit_teacher): ?>
                                <a href="teachers.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div>
                        <div class="info-box">
                            <h4>📝 Instructions</h4>
                            <ul>
                                <li>Enter the full teacher name</li>
                                <li>Select the appropriate department</li>
                                <li>Teachers cannot be deleted if they have scheduled classes</li>
                                <li>You can edit teacher information anytime</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>📋 All Teachers</h2>
            </div>
            <div class="card-body">
                <?php if (empty($teachers)): ?>
                    <div class="alert alert-info">
                        No teachers found. Add your first teacher using the form above.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Teacher Name</th>
                                <th>Department</th>
                                <th>Scheduled Classes</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?php echo $teacher['teacher_id']; ?></td>
                                    <td><?php echo htmlspecialchars($teacher['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['dept']); ?></td>
                                    <td>
                                        <span class="badge"><?php echo $teacher['timetable_count']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></td>
                                    <td>
                                        <a href="teachers.php?edit=<?php echo $teacher['teacher_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <?php if ($teacher['timetable_count'] == 0): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="teacher_id" value="<?php echo $teacher['teacher_id']; ?>">
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