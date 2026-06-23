<?php
require_once 'config.php';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $room_number = sanitize_input($_POST['room_number']);
            $capacity = (int)$_POST['capacity'];
            
            if (empty($room_number) || $capacity <= 0) {
                $message = 'Please fill in all fields with valid values.';
                $message_type = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO classrooms (room_number, capacity) VALUES (?, ?)");
                    $stmt->execute([$room_number, $capacity]);
                    $message = 'Classroom added successfully!';
                    $message_type = 'success';
                } catch(PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $message = 'Room number already exists. Please use a different room number.';
                    } else {
                        $message = 'Error adding classroom: ' . $e->getMessage();
                    }
                    $message_type = 'danger';
                }
            }
        }
        
        elseif ($action == 'edit') {
            $room_id = $_POST['room_id'];
            $room_number = sanitize_input($_POST['room_number']);
            $capacity = (int)$_POST['capacity'];
            
            if (empty($room_number) || $capacity <= 0) {
                $message = 'Please fill in all fields with valid values.';
                $message_type = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE classrooms SET room_number = ?, capacity = ? WHERE room_id = ?");
                    $stmt->execute([$room_number, $capacity, $room_id]);
                    $message = 'Classroom updated successfully!';
                    $message_type = 'success';
                } catch(PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $message = 'Room number already exists. Please use a different room number.';
                    } else {
                        $message = 'Error updating classroom: ' . $e->getMessage();
                    }
                    $message_type = 'danger';
                }
            }
        }
        
        elseif ($action == 'delete') {
            $room_id = $_POST['room_id'];
            try {
                // Check if classroom has timetable entries
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM timetable WHERE room_id = ?");
                $stmt->execute([$room_id]);
                $timetable_count = $stmt->fetchColumn();
                
                if ($timetable_count > 0) {
                    $message = 'Cannot delete classroom. It has scheduled classes. Please remove timetable entries first.';
                    $message_type = 'warning';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM classrooms WHERE room_id = ?");
                    $stmt->execute([$room_id]);
                    $message = 'Classroom deleted successfully!';
                    $message_type = 'success';
                }
            } catch(PDOException $e) {
                $message = 'Error deleting classroom: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Get all classrooms
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(tt.tt_id) as timetable_count FROM classrooms c LEFT JOIN timetable tt ON c.room_id = tt.room_id GROUP BY c.room_id ORDER BY c.room_number");
    $classrooms = $stmt->fetchAll();
} catch(PDOException $e) {
    $classrooms = [];
    $message = 'Error fetching classrooms: ' . $e->getMessage();
    $message_type = 'danger';
}

// Get classroom for editing
$edit_classroom = null;
if (isset($_GET['edit'])) {
    $room_id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM classrooms WHERE room_id = ?");
        $stmt->execute([$room_id]);
        $edit_classroom = $stmt->fetch();
    } catch(PDOException $e) {
        $message = 'Error fetching classroom: ' . $e->getMessage();
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classrooms Management - Timetable System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>📅 Timetable Management System</h1>
            <p>Efficiently manage courses, teachers, classrooms, and schedules</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">🏠 Dashboard</a></li>
                <li><a href="courses.php">📚 Courses</a></li>
                <li><a href="teachers.php">👨‍🏫 Teachers</a></li>
                <li><a href="classrooms.php" class="active">🏫 Classrooms</a></li>
                <li><a href="timeslots.php">⏰ Time Slots</a></li>
                <li><a href="timetable.php">📋 Timetable</a></li>
                <li><a href="view_timetable.php">👁️ View Schedule</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>🏫 Classroom Management</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add/Edit Classroom Form -->
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?php echo $edit_classroom ? 'Edit Classroom' : 'Add New Classroom'; ?></h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $edit_classroom ? 'edit' : 'add'; ?>">
                                <?php if ($edit_classroom): ?>
                                    <input type="hidden" name="room_id" value="<?php echo $edit_classroom['room_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="room_number">Room Number:</label>
                                    <input type="text" id="room_number" name="room_number" class="form-control" 
                                           value="<?php echo $edit_classroom ? htmlspecialchars($edit_classroom['room_number']) : ''; ?>" 
                                           placeholder="e.g., A101, B201, C301" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="capacity">Capacity:</label>
                                    <input type="number" id="capacity" name="capacity" class="form-control" 
                                           value="<?php echo $edit_classroom ? $edit_classroom['capacity'] : ''; ?>" 
                                           min="1" max="500" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <?php echo $edit_classroom ? 'Update Classroom' : 'Add Classroom'; ?>
                                </button>
                                
                                <?php if ($edit_classroom): ?>
                                    <a href="classrooms.php" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h3>Classroom Information</h3>
                            <div class="alert alert-info">
                                <strong>Instructions:</strong>
                                <ul>
                                    <li>Enter a unique room number (e.g., A101, B201)</li>
                                    <li>Specify the maximum capacity of the room</li>
                                    <li>Room numbers must be unique</li>
                                    <li>Classrooms cannot be deleted if they have scheduled classes</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Capacity Guidelines:</strong>
                                <ul>
                                    <li>Small rooms: 20-40 students</li>
                                    <li>Medium rooms: 40-80 students</li>
                                    <li>Large rooms: 80+ students</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classrooms List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Classrooms</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($classrooms)): ?>
                        <div class="alert alert-info">
                            No classrooms found. Add your first classroom using the form above.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Room Number</th>
                                        <th>Capacity</th>
                                        <th>Scheduled Classes</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classrooms as $classroom): ?>
                                        <tr>
                                            <td><?php echo $classroom['room_id']; ?></td>
                                            <td><?php echo htmlspecialchars($classroom['room_number']); ?></td>
                                            <td><?php echo $classroom['capacity']; ?> students</td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $classroom['timetable_count']; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($classroom['created_at'])); ?></td>
                                            <td>
                                                <a href="classrooms.php?edit=<?php echo $classroom['room_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <?php if ($classroom['timetable_count'] == 0): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this classroom?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="room_id" value="<?php echo $classroom['room_id']; ?>">
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
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer style="background-color: #2c3e50; color: white; text-align: center; padding: 1rem 0; margin-top: 2rem;">
        <div class="container">
            <p>&copy; 2024 Timetable Management System - DBMS Course Project</p>
        </div>
    </footer>

    <style>
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
        }
        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }
    </style>
</body>
</html>
