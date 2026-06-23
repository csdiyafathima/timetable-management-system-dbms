<?php
require_once 'config.php';

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $course_name = sanitize_input($_POST['course_name']);
            $dept = sanitize_input($_POST['dept']);
            
            if (empty($course_name) || empty($dept)) {
                $message = 'Please fill in all fields.';
                $message_type = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO courses (course_name, dept) VALUES (?, ?)");
                    $stmt->execute([$course_name, $dept]);
                    $message = 'Course added successfully!';
                    $message_type = 'success';
                } catch(PDOException $e) {
                    $message = 'Error adding course: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
        
        elseif ($action == 'edit') {
            $course_id = $_POST['course_id'];
            $course_name = sanitize_input($_POST['course_name']);
            $dept = sanitize_input($_POST['dept']);
            
            if (empty($course_name) || empty($dept)) {
                $message = 'Please fill in all fields.';
                $message_type = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE courses SET course_name = ?, dept = ? WHERE course_id = ?");
                    $stmt->execute([$course_name, $dept, $course_id]);
                    $message = 'Course updated successfully!';
                    $message_type = 'success';
                } catch(PDOException $e) {
                    $message = 'Error updating course: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        }
        
        elseif ($action == 'delete') {
            $course_id = $_POST['course_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
                $stmt->execute([$course_id]);
                $message = 'Course deleted successfully!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error deleting course: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Get all courses
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY course_name");
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    $courses = [];
    $message = 'Error fetching courses: ' . $e->getMessage();
    $message_type = 'danger';
}

// Get course for editing
$edit_course = null;
if (isset($_GET['edit'])) {
    $course_id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $edit_course = $stmt->fetch();
    } catch(PDOException $e) {
        $message = 'Error fetching course: ' . $e->getMessage();
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management - Timetable System</title>
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
                <li><a href="courses.php" class="active">📚 Courses</a></li>
                <li><a href="teachers.php">👨‍🏫 Teachers</a></li>
                <li><a href="classrooms.php">🏫 Classrooms</a></li>
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
                    <h2>📚 Course Management</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add/Edit Course Form -->
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?></h3>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?php echo $edit_course ? 'edit' : 'add'; ?>">
                                <?php if ($edit_course): ?>
                                    <input type="hidden" name="course_id" value="<?php echo $edit_course['course_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="course_name">Course Name:</label>
                                    <input type="text" id="course_name" name="course_name" class="form-control" 
                                           value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dept">Department:</label>
                                    <input type="text" id="dept" name="dept" class="form-control" 
                                           value="<?php echo $edit_course ? htmlspecialchars($edit_course['dept']) : ''; ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>
                                </button>
                                
                                <?php if ($edit_course): ?>
                                    <a href="courses.php" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h3>Course Information</h3>
                            <div class="alert alert-info">
                                <strong>Instructions:</strong>
                                <ul>
                                    <li>Enter the full course name</li>
                                    <li>Specify the department (e.g., Computer Science, Mathematics)</li>
                                    <li>Course names should be unique</li>
                                    <li>You can edit courses by clicking the edit button</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses List -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Courses</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($courses)): ?>
                        <div class="alert alert-info">
                            No courses found. Add your first course using the form above.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Course Name</th>
                                        <th>Department</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?php echo $course['course_id']; ?></td>
                                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['dept']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                            <td>
                                                <a href="courses.php?edit=<?php echo $course['course_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
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
</body>
</html>
