<?php
session_start();
require_once 'config/database.php';

// Check if faculty is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit();
}

// Get faculty information
$stmt = $conn->prepare("SELECT * FROM faculty WHERE id = ?");
$stmt->execute([$_SESSION['faculty_id']]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize default values if faculty data is incomplete
$faculty = array_merge([
    'fname' => 'Faculty',
    'lname' => 'Member',
    'mi' => '',
], $faculty);

function getGrade($grade) {
    if ($grade >= 97.50 && $grade <= 100) return "1.00";
    elseif ($grade >= 94.50 && $grade < 97.49) return "1.25";
    elseif ($grade >= 91.50 && $grade < 94.49) return "1.50";
    elseif ($grade >= 88.50 && $grade < 91.49) return "1.75";
    elseif ($grade >= 85.50 && $grade < 88.49) return "2.00";
    elseif ($grade >= 82.50 && $grade < 85.49) return "2.25";
    elseif ($grade >= 79.50 && $grade < 82.49) return "2.50";
    elseif ($grade >= 76.50 && $grade < 79.49) return "2.75";
    elseif ($grade >= 74.50 && $grade < 76.49) return "3.00";
    else return "5.00";
}

// Get all students for dropdown
$students = $conn->query("SELECT * FROM student ORDER BY lname")->fetchAll(PDO::FETCH_ASSOC);

// Handle student selection
if (isset($_GET['student_id'])) {
    // Get student details
    $stmt = $conn->prepare("
        SELECT s.*, c.course_name 
        FROM student s 
        LEFT JOIN course c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$_GET['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get student grades
    $stmt = $conn->prepare("
        SELECT g.*, c.course_name,
               CONCAT(f.fname, ' ', f.mi, '. ', f.lname) as faculty_name
        FROM grade g
        JOIN course c ON g.course_id = c.id
        JOIN faculty f ON g.faculty_id = f.id
        WHERE g.student_id = ?
        ORDER BY g.semester, g.term
    ");
    $stmt->execute([$_GET['student_id']]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate GWA
    $total_units = 0;
    $total_weighted_grade = 0;
    foreach ($grades as $grade) {
        // Assuming each course is 3 units
        $units = 3;
        $total_units += $units;
        $total_weighted_grade += ($units * floatval($grade['grade_equivalent']));
    }
    $gwa = $total_units > 0 ? number_format($total_weighted_grade / $total_units, 2) : 0;
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

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group select:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
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

        tr:last-child td {
            border-bottom: none;
        }

        .student-info {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .student-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 600;
        }

        .student-info p {
            margin-bottom: 10px;
            color: #34495e;
            font-size: 15px;
            line-height: 1.5;
        }

        .student-info strong {
            font-weight: 600;
            margin-right: 5px;
        }

        .gwa-display {
            background-color: #2ecc71;
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .gwa-display h3 {
            margin-bottom: 15px;
            font-size: 22px;
            font-weight: 600;
        }

        .gwa-display .number {
            font-size: 42px;
            font-weight: bold;
            margin: 10px 0;
        }

        .passed {
            color: #27ae60;
            font-weight: bold;
        }

        .failed {
            color: #c0392b;
            font-weight: bold;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .grade-table th,
        .grade-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .grade-table .header th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            border: none;
            padding: 18px 15px;
        }

        .grade-table .period {
            width: 25%;
        }

        .grade-table .grade {
            width: 37.5%;
        }

        .grade-table .period-name {
            font-weight: 600;
            color: #2c3e50;
            background-color: #f8f9fa;
        }

        .grade-table .center {
            text-align: center;
            font-size: 15px;
        }

        .grade-table .semester-grade {
            background-color: #34495e;
        }

        .grade-table .semester-grade td {
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 18px;
        }

        .grade-table .final-rating {
            background-color: #2ecc71;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        .grade-table .final-rating.failed {
            background-color: #e74c3c;
        }

        .grade-table .final-rating td {
            padding: 20px;
            font-size: 18px;
            letter-spacing: 1px;
            border: none;
        }

        .grade-table tr:hover:not(.header):not(.semester-grade):not(.final-rating) {
            background-color: #f5f6fa;
            transition: background-color 0.3s ease;
        }

        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            font-size: 15px;
            line-height: 1.5;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
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
            <h1>General Weighted Average (GWA)</h1>
            
            <div class="form-container">
                <form method="GET">
                    <div class="form-group">
                        <label for="student_id">Select Student</label>
                        <select id="student_id" name="student_id" onchange="this.form.submit()">
                            <option value="">Choose a student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo (isset($_GET['student_id']) && $_GET['student_id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['lname'] . ', ' . $s['fname'] . ' ' . $s['mi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['student_id']) && !empty($_GET['student_id'])): ?>
                <?php if ($student): ?>
                    <div class="student-info">
                        <h3>Student Information</h3>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? ''); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars(($student['fname'] ?? '') . ' ' . ($student['mi'] ?? '') . '. ' . ($student['lname'] ?? '')); ?></p>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course_name'] ?? ''); ?></p>
                    </div>

                    <?php if (!empty($grades)): ?>
                        <div class="gwa-display">
                            <h3>General Weighted Average</h3>
                            <div class="number"><?php echo $gwa; ?></div>
                            <p><?php echo ($gwa < 3.0) ? 'Good Standing' : 'Warning Status'; ?></p>
                        </div>
                    <?php endif; ?>

                    <table class="grade-table">
                        <tr class="header">
                            <th class="period">Grading<br>Period</th>
                            <th class="grade">Numeric Grade</th>
                            <th class="grade">Grade Equivalent</th>
                        </tr>
                        <?php
                            $prelim = array_filter($grades ?? [], function($grade) { return $grade['term'] === 'prelim'; });
                            $midterm = array_filter($grades ?? [], function($grade) { return $grade['term'] === 'midterm'; });
                            $final = array_filter($grades ?? [], function($grade) { return $grade['term'] === 'final'; });
                            
                            $prelim_grade = !empty($prelim) ? current($prelim)['numeric_grade'] : 0;
                            $midterm_grade = !empty($midterm) ? current($midterm)['numeric_grade'] : 0;
                            $final_grade = !empty($final) ? current($final)['numeric_grade'] : 0;
                            
                            // Calculate semester grade
                            $semester_grade = ($prelim_grade * 0.3) + ($midterm_grade * 0.3) + ($final_grade * 0.4);
                            if ($prelim_grade < 74.50 || $midterm_grade < 74.50 || $final_grade < 74.50) {
                                $semester_grade = 70.00;
                            }
                        ?>
                        <tr>
                            <td class="period-name">Prelim</td>
                            <td class="center"><?php echo number_format($prelim_grade, 2); ?></td>
                            <td class="center"><?php echo !empty($prelim) ? current($prelim)['grade_equivalent'] : '5.00'; ?></td>
                        </tr>
                        <tr>
                            <td class="period-name">Midterm</td>
                            <td class="center"><?php echo number_format($midterm_grade, 2); ?></td>
                            <td class="center"><?php echo !empty($midterm) ? current($midterm)['grade_equivalent'] : '5.00'; ?></td>
                        </tr>
                        <tr>
                            <td class="period-name">Final</td>
                            <td class="center"><?php echo number_format($final_grade, 2); ?></td>
                            <td class="center"><?php echo !empty($final) ? current($final)['grade_equivalent'] : '5.00'; ?></td>
                        </tr>
                        <tr class="semester-grade">
                            <td class="period-name" style="color: white; background-color: transparent;">Semester Grade</td>
                            <td class="center"><?php echo number_format($semester_grade, 2); ?></td>
                            <td class="center"><?php echo ($semester_grade < 74.50) ? "5.00" : getGrade($semester_grade); ?></td>
                        </tr>
                        <tr class="final-rating <?php echo ($semester_grade < 74.50) ? 'failed' : ''; ?>">
                            <td colspan="3">Final Rating: <?php echo ($semester_grade < 74.50) ? "FAILED" : "PASSED"; ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="alert">
                        <p>No student information found for the selected ID.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 