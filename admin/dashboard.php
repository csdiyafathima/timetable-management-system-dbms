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
    <title>Admin Dashboard - Timetable Management System </title>
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

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-card h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            color: #666;
            font-size: 1.1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .dashboard-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .card-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 Admin Dashboard</h1>
        <div class="header-info">
            <div class="date-time">📅 <?php echo $currentDate; ?></div>
            <div class="date-time">🕐 <?php echo $currentTime; ?></div>
            <div>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</div>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h2>Welcome to Timetable Management System</h2>
            <p>Manage your institution's timetable efficiently</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM teachers");
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-label">Teachers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM courses");
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM classrooms");
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-label">Classrooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM timeslots");
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-label">Time Slots</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a href="teachers.php" class="dashboard-card">
                <span class="card-icon">👨‍🏫</span>
                <div class="card-title">Manage Teachers</div>
                <div class="card-description">Add, edit, or remove teachers from the system</div>
            </a>

            <a href="timeslots.php" class="dashboard-card">
                <span class="card-icon">⏰</span>
                <div class="card-title">Manage Time Slots</div>
                <div class="card-description">Configure available time slots for scheduling</div>
            </a>

            <a href="timetable.php" class="dashboard-card">
                <span class="card-icon">📋</span>
                <div class="card-title">Create Timetable</div>
                <div class="card-description">Build and manage class schedules</div>
            </a>

            <a href="view_timetable.php" class="dashboard-card">
                <span class="card-icon">👁️</span>
                <div class="card-title">View Timetable</div>
                <div class="card-description">Preview and analyze current schedules</div>
            </a>

            <a href="settings.php" class="dashboard-card">
                <span class="card-icon">⚙️</span>
                <div class="card-title">Settings</div>
                <div class="card-description">Configure system preferences and options</div>
            </a>

            <a href="../user/view_timetable.php" class="dashboard-card">
                <span class="card-icon">👤</span>
                <div class="card-title">User View</div>
                <div class="card-description">See how users view the timetable</div>
            </a>
        </div>
    </div>
</body>
</html>
