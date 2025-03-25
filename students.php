<?php
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // First insert the student
                $stmt = $pdo->prepare("INSERT INTO student (student_id, lname, fname, mi) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['student_id'],
                    $_POST['lname'],
                    $_POST['fname'],
                    $_POST['mi']
                ]);
                
                $studentId = $pdo->lastInsertId();
                
                // Then insert course relationships if courses were selected
                if (isset($_POST['course_ids']) && is_array($_POST['course_ids'])) {
                    $stmt = $pdo->prepare("INSERT INTO student_course (student_id, course_id) VALUES (?, ?)");
                    foreach($_POST['course_ids'] as $courseId) {
                        $stmt->execute([$studentId, $courseId]);
                    }
                }
                
                $pdo->commit();
            } catch(Exception $e) {
                $pdo->rollBack();
                echo "Error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'edit') {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // First update the student
                $stmt = $pdo->prepare("UPDATE student SET student_id = ?, lname = ?, fname = ?, mi = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['student_id'],
                    $_POST['lname'],
                    $_POST['fname'],
                    $_POST['mi'],
                    $_POST['id']
                ]);
                
                // Delete existing course relationships
                $stmt = $pdo->prepare("DELETE FROM student_course WHERE student_id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Insert new course relationships
                if (isset($_POST['course_ids']) && is_array($_POST['course_ids'])) {
                    $stmt = $pdo->prepare("INSERT INTO student_course (student_id, course_id) VALUES (?, ?)");
                    foreach($_POST['course_ids'] as $courseId) {
                        $stmt->execute([$_POST['id'], $courseId]);
                    }
                }
                
                $pdo->commit();
            } catch(Exception $e) {
                $pdo->rollBack();
                echo "Error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete') {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // First delete course relationships
                $stmt = $pdo->prepare("DELETE FROM student_course WHERE student_id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Then delete the student
                $stmt = $pdo->prepare("DELETE FROM student WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                $pdo->commit();
            } catch(Exception $e) {
                $pdo->rollBack();
                echo "Error: " . $e->getMessage();
            }
        }
    }
}

// Get all courses for dropdown
$courses = $pdo->query("SELECT * FROM course")->fetchAll(PDO::FETCH_ASSOC);

// Create student_course junction table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_course (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            FOREIGN KEY (student_id) REFERENCES student(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE,
            UNIQUE KEY unique_student_course (student_id, course_id)
        )
    ");
} catch(Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

// Get all students with their courses
$students = $pdo->query("
    SELECT s.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as courses
    FROM student s 
    LEFT JOIN student_course sc ON s.id = sc.student_id
    LEFT JOIN course c ON sc.course_id = c.id
    GROUP BY s.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Students</title>
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

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
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

        .edit-btn {
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

        .edit-btn:hover {
            background-color: #27ae60;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 7% auto;
            padding: 30px;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
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
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        .modal-title {
            margin-bottom: 25px;
            color: #2c3e50;
            font-size: 22px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
            
            .sidebar {
                width: 200px;
            }
        }

        /* Checkbox Styles */
        .checkbox-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 6px;
            background-color: #f9f9f9;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 6px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .checkbox-item:hover {
            background-color: #edf2f7;
        }
        
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        .checkbox-item label {
            cursor: pointer;
            user-select: none;
            font-size: 14px;
            color: #333;
        }
        
        /* Accordion Styles */
        .course-accordion {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        
        .course-preview {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80%;
            padding: 5px 0;
            color: #2c3e50;
            flex-grow: 1;
        }
        
        .course-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            color: white;
            background-color: #3498db;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .course-toggle:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .course-toggle:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .course-full {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            z-index: 10;
            width: 100%;
            min-width: 250px;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #e1e1e1;
            margin-top: 10px;
        }
        
        .course-full div {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .course-full div:last-child {
            border-bottom: none;
        }
        
        .show-courses {
            display: block;
            animation: fadeIn 0.25s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
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
            <div class="menu-item active">
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
            <h1>Student Management</h1>
            
            <div class="form-container">
                <h2>Add New Student</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id">Student ID</label>
                            <input type="text" id="student_id" name="student_id" required>
                        </div>
                        <div class="form-group">
                            <label for="lname">Last Name</label>
                            <input type="text" id="lname" name="lname" required>
                        </div>
                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" id="fname" name="fname" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mi">Middle Initial</label>
                            <input type="text" id="mi" name="mi" maxlength="1">
                        </div>
                        <div class="form-group">
                            <label>Courses</label>
                            <div class="checkbox-container">
                                <?php foreach ($courses as $course): ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="course_<?php echo $course['id']; ?>" name="course_ids[]" value="<?php echo $course['id']; ?>">
                                        <label for="course_<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn">Add Student</button>
                </form>
            </div>

            <h2>Student List</h2>
            <div class="table-container">
                <table id="studentsTable" class="display responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Courses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['fname'] . ' ' . $student['mi'] . '. ' . $student['lname']); ?></td>
                                <td>
                                    <?php if(!empty($student['courses'])): ?>
                                        <div class="course-accordion">
                                            <span class="course-preview">
                                                <?php 
                                                    $coursesArray = explode(', ', $student['courses']);
                                                    $courseCount = count($coursesArray);
                                                    echo htmlspecialchars($coursesArray[0]);
                                                    if($courseCount > 1) {
                                                        echo " <small>+" . ($courseCount - 1) . " more</small>";
                                                    }
                                                ?>
                                            </span>
                                            <button type="button" class="course-toggle" title="View all courses" onclick="toggleCourses(this)">+</button>
                                            <div class="course-full">
                                                <h4 style="margin-bottom: 10px; color: #3498db; border-bottom: 1px solid #eee; padding-bottom: 8px;">Enrolled Courses</h4>
                                                <?php foreach($coursesArray as $course): ?>
                                                    <div><?php echo htmlspecialchars($course); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #777;">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="edit-btn" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">Edit</button>
                                    <button class="delete-btn" onclick="deleteStudent(<?php echo $student['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Edit Student</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_student_id">Student ID</label>
                        <input type="text" id="edit_student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_lname">Last Name</label>
                        <input type="text" id="edit_lname" name="lname" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_fname">First Name</label>
                        <input type="text" id="edit_fname" name="fname" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_mi">Middle Initial</label>
                        <input type="text" id="edit_mi" name="mi" maxlength="1">
                    </div>
                    <div class="form-group">
                        <label>Courses</label>
                        <div class="checkbox-container">
                            <?php foreach ($courses as $course): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="edit_course_<?php echo $course['id']; ?>" name="course_ids[]" value="<?php echo $course['id']; ?>">
                                    <label for="edit_course_<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn">Update Student</button>
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
            $('#studentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    search: "Search students:",
                    lengthMenu: "Show _MENU_ students per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ students",
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
        const modal = document.getElementById('editModal');
        const span = document.getElementsByClassName('close')[0];

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

        function editStudent(student) {
            // Populate the form with student data
            document.getElementById('edit_id').value = student.id;
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_lname').value = student.lname;
            document.getElementById('edit_fname').value = student.fname;
            document.getElementById('edit_mi').value = student.mi;
            
            // Reset all checkboxes
            document.querySelectorAll('input[id^="edit_course_"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Fetch current courses for this student
            fetch(`get_student_courses.php?student_id=${student.id}`)
                .then(response => response.json())
                .then(data => {
                    // Check the checkboxes for the courses this student is enrolled in
                    data.forEach(courseId => {
                        const checkbox = document.getElementById(`edit_course_${courseId}`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                })
                .catch(error => console.error('Error fetching courses:', error));
            
            // Display the modal
            modal.style.display = "block";
        }

        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleCourses(button) {
            const courseFull = button.nextElementSibling;
            
            // Close any other open course listings
            document.querySelectorAll('.course-full.show-courses').forEach(element => {
                if (element !== courseFull) {
                    element.classList.remove('show-courses');
                    const toggleBtn = element.previousElementSibling;
                    if (toggleBtn && toggleBtn.classList.contains('course-toggle')) {
                        toggleBtn.textContent = '+';
                    }
                }
            });
            
            // Toggle the current one
            if (courseFull.classList.contains('show-courses')) {
                courseFull.classList.remove('show-courses');
                button.textContent = '+';
            } else {
                courseFull.classList.add('show-courses');
                button.textContent = '−'; // Using minus sign
            }
        }
        
        // Close any open course listings when clicking elsewhere on the page
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.course-toggle')) {
                document.querySelectorAll('.course-full.show-courses').forEach(element => {
                    element.classList.remove('show-courses');
                    const toggleBtn = element.previousElementSibling;
                    if (toggleBtn && toggleBtn.classList.contains('course-toggle')) {
                        toggleBtn.textContent = '+';
                    }
                });
            }
        }, true);
    </script>
</body>
</html> 