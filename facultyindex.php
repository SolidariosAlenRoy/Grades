<?php
session_start();
require_once 'config/database.php';

// Check if faculty is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit();
}

// Get faculty information with courses
$stmt = $conn->prepare("
    SELECT f.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as courses
    FROM faculty f 
    LEFT JOIN faculty_course fc ON f.id = fc.faculty_id
    LEFT JOIN course c ON fc.course_id = c.id
    WHERE f.id = ?
    GROUP BY f.id
");
$stmt->execute([$_SESSION['faculty_id']]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faculty) {
    // If faculty record doesn't exist, create a basic one
    $stmt = $conn->prepare("INSERT INTO faculty (id, email) VALUES (?, ?)");
    $stmt->execute([$_SESSION['faculty_id'], $_SESSION['email']]);
    
    // Fetch the newly created faculty record
    $stmt = $conn->prepare("
        SELECT f.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as courses
        FROM faculty f 
        LEFT JOIN faculty_course fc ON f.id = fc.faculty_id
        LEFT JOIN course c ON fc.course_id = c.id
        WHERE f.id = ?
        GROUP BY f.id
    ");
    $stmt->execute([$_SESSION['faculty_id']]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize default values if faculty data is incomplete
$faculty = array_merge([
    'fname' => 'Faculty',
    'lname' => 'Member',
    'mi' => '',
    'courses' => 'Not Assigned',
    'faculty_id' => 'Not Set'
], $faculty);

// Get students for the faculty's courses
$students = [];
if ($faculty['courses'] && $faculty['courses'] !== 'Not Assigned') {
    $courseNames = explode(', ', $faculty['courses']);
    $placeholders = str_repeat('?,', count($courseNames) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT DISTINCT s.*, c.course_name 
        FROM student s 
        JOIN student_course sc ON s.id = sc.student_id
        JOIN course c ON sc.course_id = c.id 
        WHERE c.course_name IN ($placeholders)
    ");
    $stmt->execute($courseNames);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all faculty members with their courses
$stmt = $conn->query("
    SELECT f.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as courses
    FROM faculty f 
    LEFT JOIN faculty_course fc ON f.id = fc.faculty_id
    LEFT JOIN course c ON fc.course_id = c.id
    GROUP BY f.id
    ORDER BY f.lname
");
$faculty_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get faculty's courses
$courses = [];
if ($faculty['courses'] && $faculty['courses'] !== 'Not Assigned') {
    $courseNames = explode(', ', $faculty['courses']);
    $placeholders = str_repeat('?,', count($courseNames) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT c.* 
        FROM course c 
        WHERE c.course_name IN ($placeholders)
    ");
    $stmt->execute($courseNames);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get grades for the faculty's courses
$grades = [];
if ($faculty['courses'] && $faculty['courses'] !== 'Not Assigned') {
    $courseNames = explode(', ', $faculty['courses']);
    $placeholders = str_repeat('?,', count($courseNames) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT g.*, s.student_id, CONCAT(s.fname, ' ', s.mi, '. ', s.lname) as student_name 
        FROM grade g 
        JOIN student s ON g.student_id = s.id 
        JOIN student_course sc ON s.id = sc.student_id
        JOIN course c ON sc.course_id = c.id
        WHERE c.course_name IN ($placeholders) AND g.faculty_id = ?
    ");
    $params = array_merge($courseNames, [$_SESSION['faculty_id']]);
    $stmt->execute($params);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
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

        .content h2 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #2c3e50;
            font-weight: 600;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
        }

        .table-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-success {
            background-color: #2ecc71;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .welcome-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .welcome-section p {
            color: #7f8c8d;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Faculty Dashboard</h1>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($faculty['fname'] . ' ' . $faculty['lname']); ?></span>
        </div>
    </header>

    <div class="main-container">
        <div class="sidebar">
            <h2>Faculty Menu</h2>
            <div class="menu-item active">
                <a href="facultyindex.php">Dashboard</a>
            </div>
            <div class="menu-item">
                <a href="#students">Student List</a>
                        </div>
            <div class="menu-item">
                <a href="#faculty">Faculty List</a>
                        </div>
            <div class="menu-item">
                <a href="#courses">My Courses</a>
                    </div>
            <div class="menu-item">
                <a href="grades.php">Grade Submission</a>
                        </div>
            <div class="menu-item">
                <a href="gwa.php">GWA Computation</a>
                        </div>
            <div class="menu-item">
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="content">
            <div class="welcome-section">
                <h2>Welcome, <?php echo htmlspecialchars($faculty['fname'] . ' ' . $faculty['lname']); ?></h2>
                <p>Course: <?php echo htmlspecialchars($faculty['courses']); ?></p>
                </div>

                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <h3>Total Students</h3>
                    <div class="number"><?php echo count($students); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Faculty</h3>
                    <div class="number"><?php echo count($faculty_list); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>My Courses</h3>
                    <div class="number"><?php echo count($courses); ?></div>
                    </div>
                </div>

            <div id="students" class="table-container">
                    <h2>Student List</h2>
                <table id="studentsTable" class="display responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Course</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['fname'] . ' ' . $student['mi'] . '. ' . $student['lname']); ?></td>
                                <td>
                                    <?php if(!empty($student['course_name'])): ?>
                                        <div class="course-accordion">
                                            <span class="course-preview">
                                                <?php echo htmlspecialchars($student['course_name']); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #777;">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="gwa.php?student_id=<?php echo $student['id']; ?>" class="btn btn-success">View Grades</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <div id="faculty" class="table-container">
                    <h2>Faculty List</h2>
                <table id="facultyTable" class="display responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Faculty ID</th>
                                <th>Name</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($faculty_list as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['faculty_id']); ?></td>
                                <td><?php echo htmlspecialchars($member['fname'] . ' ' . $member['mi'] . '. ' . $member['lname']); ?></td>
                                <td><?php echo htmlspecialchars($member['courses']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <div id="courses" class="table-container">
                    <h2>My Courses</h2>
                <table id="coursesTable" class="display responsive nowrap" style="width:100%">
                        <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                                <td>
                                    <a href="grades.php?course_id=<?php echo $course['id']; ?>" class="btn">Manage Grades</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#studentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
            });

            $('#facultyTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
            });

            $('#coursesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
            });

            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 500);
                }
            });
        });
    </script>
</body>
</html>
