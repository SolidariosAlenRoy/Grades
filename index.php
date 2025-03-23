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
            font-family: Arial, sans-serif;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            min-height: 100vh;
            color: white;
            padding: 20px 0;
        }

        .sidebar h2 {
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .menu-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .menu-item:hover {
            background-color: #34495e;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .content {
            flex: 1;
            padding: 20px;
            background-color: #f5f6fa;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }

        .calendar {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .calendar h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Student Management</h2>
        <div class="menu-item">
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

    <script>
        // Calendar initialization can be added here
    </script>
</body>
</html> 