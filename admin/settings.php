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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Timetable Management System</title>
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

        .logout-btn {
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
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .settings-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .settings-card h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
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
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 5px 5px 0;
        }

        .info-box h4 {
            color: #1976d2;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: #666;
            margin: 0.25rem 0;
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

            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ System Settings</h1>
        <div class="header-info">
            <div class="date-time">📅 <?php echo $currentDate; ?></div>
            <div class="date-time">🕐 <?php echo $currentTime; ?></div>
            <div>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</div>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="info-box">
            <h4>📊 System Information</h4>
            <p><strong>Database:</strong> timetable_db</p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server Time:</strong> <?php echo $currentDate . ' ' . $currentTime; ?></p>
            <p><strong>Timezone:</strong> Asia/Kolkata</p>
        </div>

        <div class="settings-grid">
            <div class="settings-card">
                <h3>🏫 Institution Settings</h3>
                <form>
                    <div class="form-group">
                        <label for="institution_name">Institution Name</label>
                        <input type="text" id="institution_name" value="Timetable Management System" readonly>
                    </div>
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <input type="text" id="academic_year" value="2024-2025" readonly>
                    </div>
                    <div class="form-group">
                        <label for="semester">Current Semester</label>
                        <select id="semester">
                            <option value="1">Semester 1</option>
                            <option value="2" selected>Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                        </select>
                    </div>
                    <button type="button" class="btn">Save Changes</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>⏰ Time Settings</h3>
                <form>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone">
                            <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="working_days">Working Days</label>
                        <input type="text" id="working_days" value="Monday to Saturday" readonly>
                    </div>
                    <div class="form-group">
                        <label for="class_duration">Class Duration (minutes)</label>
                        <input type="number" id="class_duration" value="90" min="30" max="180">
                    </div>
                    <button type="button" class="btn">Save Changes</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>👥 User Management</h3>
                <form>
                    <div class="form-group">
                        <label for="max_users">Maximum Users</label>
                        <input type="number" id="max_users" value="100" min="1" max="1000">
                    </div>
                    <div class="form-group">
                        <label for="session_timeout">Session Timeout (minutes)</label>
                        <input type="number" id="session_timeout" value="30" min="5" max="120">
                    </div>
                    <div class="form-group">
                        <label for="password_policy">Password Policy</label>
                        <select id="password_policy">
                            <option value="simple">Simple (6+ characters)</option>
                            <option value="medium" selected>Medium (8+ characters, mixed case)</option>
                            <option value="strong">Strong (8+ characters, numbers, symbols)</option>
                        </select>
                    </div>
                    <button type="button" class="btn">Save Changes</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>📊 Database Statistics</h3>
                <div class="info-box">
                    <h4>Current Data</h4>
                    <p><strong>Teachers:</strong> <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM teachers");
                        echo $result->fetch_assoc()['count'];
                    ?></p>
                    <p><strong>Courses:</strong> <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM courses");
                        echo $result->fetch_assoc()['count'];
                    ?></p>
                    <p><strong>Classrooms:</strong> <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM classrooms");
                        echo $result->fetch_assoc()['count'];
                    ?></p>
                    <p><strong>Time Slots:</strong> <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM timeslots");
                        echo $result->fetch_assoc()['count'];
                    ?></p>
                    <p><strong>Timetable Entries:</strong> <?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM timetable");
                        echo $result->fetch_assoc()['count'];
                    ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
