<?php
require_once 'config/database.php';

// Get counts for dashboard
$stmt = $pdo->query("SELECT COUNT(*) as count FROM student");
$studentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM faculty");
$facultyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM course");

$courseCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f5f6fa;
        }

        .header {
            background-color: #3498db;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info .user-name {
            margin-right: 10px;
        }

        .main-container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            height: calc(100vh - 62px);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 62px;
            overflow-y: auto;
        }

        .sidebar h2 {
            padding: 0 20px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .menu-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .menu-item:hover {
            background-color: #34495e;
            border-left: 4px solid #3498db;
        }

        .menu-item.active {
            background-color: #34495e;
            border-left: 4px solid #3498db;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
            display: block;
            font-size: 15px;
        }

        .content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        .content h1 {
            margin-bottom: 25px;
            font-size: 26px;
            color: #2c3e50;
            font-weight: 600;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
        }

        .calendar {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .calendar h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Student Information System</h1>
        <div class="user-info">
            <span class="user-name">Administrator</span>
        </div>
    </header>

    <div class="main-container">
        <div class="sidebar">
            <h2>Student Management</h2>
            <div class="menu-item active">
                <a href="index.php">Dashboard</a>
            </div>
            <div class="menu-item">
                <a href="students.php">Students</a>
            </div>
            <div class="menu-item">
                <a href="faculty.php">Faculty</a>
            </div>
            <div class="menu-item">
                <a href="courses.php">Courses</a>
            </div>
            <div class="menu-item">
                <a href="grades.php">Grades</a>
            </div>
            <div class="menu-item">
                <a href="gwa.php">GWA</a>
            </div>
        </div>

        <div class="content">
            <h1>Dashboard</h1>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="number"><?php echo $studentCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Faculty</h3>
                    <div class="number"><?php echo $facultyCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Courses</h3>
                    <div class="number"><?php echo $courseCount; ?></div>
                </div>
            </div>
            <div class="calendar">
                <h3>Calendar</h3>
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <script>
        // Calendar initialization can be added here
    </script>
</body>
</html> 