<?php
// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
if (!file_exists('config/db.php')) {
    die("Database connection file not found! Please create config/db.php");
}
include('config/db.php');

// Initialize error message
$error = "";

// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username == "" || $password == "") {
        $error = "Please enter both username and password.";
    } else {
        if (!$conn) {
            die("Database connection failed!");
        }

        // Prepare SQL query
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
                exit();
            } else {
                // Check if user is a teacher
                $stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_name = ?");
                $stmt->execute([$user['username']]);
                $teacher_result = $stmt->get_result();
                
                if ($teacher_result->num_rows > 0) {
                    // User is a teacher, redirect to teacher view
                    header("Location: teacher/view_timetable.php");
                    exit();
                } else {
                    // Regular user, redirect to user view
                    header("Location: user/view_timetable.php");
                    exit();
                }
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Timetable Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error {
            color: #e74c3c;
            background: #fdf2f2;
            padding: 10px;
            border-radius: 5px;
            margin-top: 1rem;
            border-left: 4px solid #e74c3c;
        }

        .demo-credentials {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .demo-credentials h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .demo-credentials p {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📅 Timetable System</h1>
            <p>Please login to continue</p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

    </div>
</body>
</html>