<?php
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO course (course_name, course_description) VALUES (?, ?)");
            $stmt->execute([
                $_POST['course_name'],
                $_POST['course_description']
            ]);
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE course SET course_name = ?, course_description = ? WHERE id = ?");
            $stmt->execute([
                $_POST['course_name'],
                $_POST['course_description'],
                $_POST['id']
            ]);
        }
    }
}

// Get all courses
$courses = $pdo->query("SELECT * FROM course")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Courses</title>
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

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
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

        .edit-btn {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
        <h1>Course Management</h1>
        
        <div class="form-container">
            <h2>Add New Course</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" id="course_name" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="course_description">Course Description</label>
                    <textarea id="course_description" name="course_description"></textarea>
                </div>
                <button type="submit" class="btn">Add Course</button>
            </form>
        </div>

        <h2>Course List</h2>
        <table>
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

    <script>
        function editCourse(course) {
            // Implement edit functionality
            console.log('Edit course:', course);
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
                // Implement delete functionality
                console.log('Delete course:', id);
            }
        }
    </script>
</body>
</html>