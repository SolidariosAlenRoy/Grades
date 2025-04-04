<?php
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO course (course_name, course_description) VALUES (?, ?)");
            $stmt->execute([
                $_POST['course_name'],
                $_POST['course_description']
            ]);
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $conn->prepare("UPDATE course SET course_name = ?, course_description = ? WHERE id = ?");
            $stmt->execute([
                $_POST['course_name'],
                $_POST['course_description'],
                $_POST['id']
            ]);
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM course WHERE id = ?");
            $stmt->execute([$_POST['id']]);
        }
    }
}

// Get all courses
$courses = $conn->query("SELECT * FROM course")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Courses</title>
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
            color: #2c3e50;
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

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
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
            <div class="menu-item">
                <a href="deadline.php">Deadline</a>
            </div>
            <div class="menu-item">
                <a href="create_user.php">Create Account</a>
            </div>
            <div class="menu-item">
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="content">
            <h1>Course Management</h1>
            
            <div class="form-container">
                <h2>Add New Course</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" id="course_name" name="course_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_description">Course Description</label>
                            <textarea id="course_description" name="course_description"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn">Add Course</button>
                </form>
            </div>

            <h2>Course List</h2>
            <div class="table-container">
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
                                    <button class="edit-btn" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">Edit</button>
                                    <button class="delete-btn" onclick="deleteCourse(<?php echo $course['id']; ?>)">Delete</button>
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
            <h2 class="modal-title">Edit Course</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_name">Course Name</label>
                        <input type="text" id="edit_course_name" name="course_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_course_description">Course Description</label>
                        <textarea id="edit_course_description" name="course_description"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn">Update Course</button>
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
            $('#coursesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    search: "Search courses:",
                    lengthMenu: "Show _MENU_ courses per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ courses",
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

        function editCourse(course) {
            // Populate the form with course data
            document.getElementById('edit_id').value = course.id;
            document.getElementById('edit_course_name').value = course.course_name;
            document.getElementById('edit_course_description').value = course.course_description || '';

            // Display the modal
            modal.style.display = "block";
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
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
    </script>
</body>
</html>