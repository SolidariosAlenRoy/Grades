<?php
require_once 'config/database.php';

function calculateGrade($class_standing, $performance_task, $exam) {
    $class_standing_score = array_sum($class_standing) / 250 * 50 + 50;
    $performance_task_score = array_sum($performance_task) / 200 * 50 + 50;
    $exam_score = $exam / 100 * 50 + 50;
    return ($class_standing_score * 0.2) + ($performance_task_score * 0.3) + ($exam_score * 0.5);
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Calculate grades for each term
            $class_standing = [
                $_POST['assignments'],
                $_POST['seatwork'],
                $_POST['quiz'],
                $_POST['attendance'],
                $_POST['class_participation']
            ];
            $performance_task = [
                $_POST['lab_exercise'],
                $_POST['project']
            ];
            $exam = $_POST['exam'];
            
            $numeric_grade = calculateGrade($class_standing, $performance_task, $exam);
            $grade_equivalent = getGrade($numeric_grade);
            $remarks = ($numeric_grade < 74.50) ? "Failed" : "Passed";

            $stmt = $conn->prepare("INSERT INTO grade (
                student_id, course_id, faculty_id, term, semester,
                assignments, quiz, seatwork, class_participation, attendance,
                project, lab_exercise, exam, numeric_grade, grade_equivalent, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['student_id'], $_POST['course_id'], $_POST['faculty_id'], 
                $_POST['term'], $_POST['semester'],
                $_POST['assignments'], $_POST['quiz'], $_POST['seatwork'], 
                $_POST['class_participation'], $_POST['attendance'],
                $_POST['project'], $_POST['lab_exercise'], $_POST['exam'],
                $numeric_grade, $grade_equivalent, $remarks
            ]);

            // Redirect to refresh the page
            header("Location: grades.php");
            exit();
        } elseif ($_POST['action'] === 'edit') {
            // Calculate grades for each term
            $class_standing = [
                $_POST['assignments'],
                $_POST['seatwork'],
                $_POST['quiz'],
                $_POST['attendance'],
                $_POST['class_participation']
            ];
            $performance_task = [
                $_POST['lab_exercise'],
                $_POST['project']
            ];
            $exam = $_POST['exam'];
            
            $numeric_grade = calculateGrade($class_standing, $performance_task, $exam);
            $grade_equivalent = getGrade($numeric_grade);
            $remarks = ($numeric_grade < 74.50) ? "Failed" : "Passed";

            // Update the grade in the database
            $stmt = $conn->prepare("UPDATE grade SET 
                student_id = ?, 
                course_id = ?, 
                faculty_id = ?, 
                term = ?, 
                semester = ?, 
                assignments = ?,
                quiz = ?,
                seatwork = ?,
                class_participation = ?,
                attendance = ?,
                project = ?,
                lab_exercise = ?,
                exam = ?,
                numeric_grade = ?,
                grade_equivalent = ?,
                remarks = ?
                WHERE id = ?");

            $stmt->execute([
                $_POST['student_id'],
                $_POST['course_id'],
                $_POST['faculty_id'],
                $_POST['term'],
                $_POST['semester'],
                $_POST['assignments'],
                $_POST['quiz'],
                $_POST['seatwork'],
                $_POST['class_participation'],
                $_POST['attendance'],
                $_POST['project'],
                $_POST['lab_exercise'],
                $_POST['exam'],
                $numeric_grade,
                $grade_equivalent,
                $remarks,
                $_POST['grade_id']
            ]);

            // Redirect to refresh the page
            header("Location: grades.php");
            exit();
        }
    }
}

$students = $conn->query("SELECT * FROM student ORDER BY lname")->fetchAll(PDO::FETCH_ASSOC);
$courses = $conn->query("SELECT * FROM course ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);
$faculty = $conn->query("SELECT * FROM faculty ORDER BY lname")->fetchAll(PDO::FETCH_ASSOC);
$grades = $conn->query("SELECT g.*, s.student_id as student_number, 
    CONCAT(s.fname, ' ', s.mi, '. ', s.lname) as student_name,
    c.course_name, CONCAT(f.fname, ' ', f.mi, '. ', f.lname) as faculty_name
    FROM grade g JOIN student s ON g.student_id = s.id
    JOIN course c ON g.course_id = c.id
    JOIN faculty f ON g.faculty_id = f.id ORDER BY g.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Grades</title>
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

        .content h3 {
            margin: 20px 0 15px;
            font-size: 18px;
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #3498db;
            outline: none;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 15px -10px;
        }
        
        .form-group {
            flex: 1 0 300px;
            margin: 0 10px 20px 10px;
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

        .passed {
            color: #27ae60;
            font-weight: 600;
        }

        .failed {
            color: #c0392b;
            font-weight: 600;
        }

        .btn-edit {
            background-color: #2ecc71;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 8px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-edit:hover {
            background-color: #27ae60;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #ddd;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            position: absolute;
            right: 25px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        @media (max-width: 992px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .table-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        /* DataTables Styling */
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: 10px;
        }

        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 15px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            border-radius: 4px;
            margin: 0 5px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #3498db;
            color: white !important;
            border: 1px solid #3498db;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f5f6fa;
            color: #333 !important;
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
            <div class="menu-item active">
                <a href="grades.php">Grades</a>
            </div>
            <div class="menu-item">
                <a href="gwa.php">GWA</a>
            </div>
        </div>

        <div class="content">
            <h1>Grade Management</h1>
            
            <div class="form-container">
                <h2>Add New Grade</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id">Student</label>
                            <select id="student_id" name="student_id" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['lname'] . ', ' . $student['fname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="faculty_id">Faculty</label>
                            <select id="faculty_id" name="faculty_id" required>
                                <option value="">Select Faculty</option>
                                <?php foreach ($faculty as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['lname'] . ', ' . $member['fname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="term">Term</label>
                            <select id="term" name="term" required>
                                <option value="">Select Term</option>
                                <option value="prelim">Prelim</option>
                                <option value="midterm">Midterm</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st semester">1st Semester</option>
                                <option value="2nd semester">2nd Semester</option>
                            </select>
                        </div>
                    </div>

                    <h3>Grade Components</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="assignments">Assignments (30%)</label>
                            <input type="number" id="assignments" name="assignments" min="0" max="100" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quiz">Quizzes (100%)</label>
                            <input type="number" id="quiz" name="quiz" min="0" max="100" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="seatwork">Seatwork (100%)</label>
                            <input type="number" id="seatwork" name="seatwork" min="0" max="100" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="class_participation">Class Participation (10%)</label>
                            <input type="number" id="class_participation" name="class_participation" min="0" max="100" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="attendance">Attendance (10%)</label>
                            <input type="number" id="attendance" name="attendance" min="0" max="100" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="project">Project (100%)</label>
                            <input type="number" id="project" name="project" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="lab_exercise">Lab Exercise (100%)</label>
                            <input type="number" id="lab_exercise" name="lab_exercise" min="0" max="100" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="exam">Exam (100%)</label>
                            <input type="number" id="exam" name="exam" min="0" max="100" step="0.01" required>
                        </div>
                    </div>

                    <button type="submit" class="btn">Add Grade</button>
                </form>
            </div>

            <h2>Grade List</h2>
            <div class="table-container">
                <table id="gradesTable" class="display responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Faculty</th>
                            <th>Term</th>
                            <th>Semester</th>
                            <th>Numeric Grade</th>
                            <th>Grade Equivalent</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($grade['faculty_name']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($grade['term'])); ?></td>
                                <td><?php echo htmlspecialchars($grade['semester']); ?></td>
                                <td><?php echo number_format($grade['numeric_grade'], 2); ?></td>
                                <td><?php echo htmlspecialchars($grade['grade_equivalent']); ?></td>
                                <td class="<?php echo strtolower($grade['remarks']); ?>">
                                    <?php echo htmlspecialchars($grade['remarks']); ?>
                                </td>
                                <td>
                                    <button class="btn-edit" onclick="editGrade(<?php echo htmlspecialchars(json_encode($grade)); ?>)">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Grade Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Grade</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="grade_id" id="edit_grade_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_student_id">Student</label>
                        <select id="edit_student_id" name="student_id" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['lname'] . ', ' . $student['fname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_course_id">Course</label>
                        <select id="edit_course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_faculty_id">Faculty</label>
                        <select id="edit_faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculty as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['lname'] . ', ' . $member['fname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_term">Term</label>
                        <select id="edit_term" name="term" required>
                            <option value="">Select Term</option>
                            <option value="prelim">Prelim</option>
                            <option value="midterm">Midterm</option>
                            <option value="final">Final</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_semester">Semester</label>
                        <select id="edit_semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1st semester">1st Semester</option>
                            <option value="2nd semester">2nd Semester</option>
                        </select>
                    </div>
                </div>

                <h3>Grade Components</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_assignments">Assignments (30%)</label>
                        <input type="number" id="edit_assignments" name="assignments" min="0" max="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_quiz">Quizzes (100%)</label>
                        <input type="number" id="edit_quiz" name="quiz" min="0" max="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_seatwork">Seatwork (100%)</label>
                        <input type="number" id="edit_seatwork" name="seatwork" min="0" max="100" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_class_participation">Class Participation (10%)</label>
                        <input type="number" id="edit_class_participation" name="class_participation" min="0" max="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_attendance">Attendance (10%)</label>
                        <input type="number" id="edit_attendance" name="attendance" min="0" max="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_project">Project (100%)</label>
                        <input type="number" id="edit_project" name="project" min="0" max="100" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_lab_exercise">Lab Exercise (100%)</label>
                        <input type="number" id="edit_lab_exercise" name="lab_exercise" min="0" max="100" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_exam">Exam (100%)</label>
                        <input type="number" id="edit_exam" name="exam" min="0" max="100" step="0.01" required>
                    </div>
                </div>

                <button type="submit" class="btn">Update Grade</button>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#gradesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    search: "Search grades:",
                    lengthMenu: "Show _MENU_ grades per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ grades",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
        
        // Get the modal
        var modal = document.getElementById("editModal");
        var span = document.getElementsByClassName("close")[0];

        // Function to open modal and populate form
        function editGrade(grade) {
            document.getElementById("edit_grade_id").value = grade.id;
            document.getElementById("edit_student_id").value = grade.student_id;
            document.getElementById("edit_course_id").value = grade.course_id;
            document.getElementById("edit_faculty_id").value = grade.faculty_id;
            document.getElementById("edit_term").value = grade.term;
            document.getElementById("edit_semester").value = grade.semester;
            document.getElementById("edit_assignments").value = grade.assignments;
            document.getElementById("edit_quiz").value = grade.quiz;
            document.getElementById("edit_seatwork").value = grade.seatwork;
            document.getElementById("edit_class_participation").value = grade.class_participation;
            document.getElementById("edit_attendance").value = grade.attendance;
            document.getElementById("edit_project").value = grade.project;
            document.getElementById("edit_lab_exercise").value = grade.lab_exercise;
            document.getElementById("edit_exam").value = grade.exam;
            
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html> 