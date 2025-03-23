<?php
require_once 'config/database.php';

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
$students = $pdo->query("SELECT * FROM student ORDER BY lname")->fetchAll(PDO::FETCH_ASSOC);

// Handle student selection
if (isset($_GET['student_id'])) {
    // Get student details
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name 
        FROM student s 
        LEFT JOIN course c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$_GET['student_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get student grades
    $stmt = $pdo->prepare("
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
    <title>Student Management - GWA</title>
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

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .student-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .student-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .student-info p {
            margin-bottom: 5px;
            color: #34495e;
        }

        .gwa-display {
            background-color: #2ecc71;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .gwa-display h3 {
            margin-bottom: 10px;
        }

        .gwa-display .number {
            font-size: 36px;
            font-weight: bold;
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
            font-family: Arial, sans-serif;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
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
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            border: none;
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

        .grade-table tr:hover:not(.header):not(.semester-grade):not(.final-rating) {
        background-color: #f5f6fa;
        transition: background-color 0.3s ease;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
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
        <div class="alert alert-warning">
            <p>No student information found for the selected ID.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html> 