<?php
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO faculty (faculty_id, lname, fname, mi, course_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['faculty_id'],
                $_POST['lname'],
                $_POST['fname'],
                $_POST['mi'],
                $_POST['course_id']
            ]);
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE faculty SET faculty_id = ?, lname = ?, fname = ?, mi = ?, course_id = ? WHERE id = ?");
            $stmt->execute([
                $_POST['faculty_id'],
                $_POST['lname'],
                $_POST['fname'],
                $_POST['mi'],
                $_POST['course_id'],
                $_POST['id']
            ]);
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM faculty WHERE id = ?");
            $stmt->execute([$_POST['id']]);
        }
    }
}

// Get all courses for dropdown
$courses = $pdo->query("SELECT * FROM course")->fetchAll(PDO::FETCH_ASSOC);

// Get all faculty members
$faculty = $pdo->query("
    SELECT f.*, c.course_name 
    FROM faculty f 
    LEFT JOIN course c ON f.course_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Faculty</title>
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

        .form-group input, .form-group select {
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
            margin: 5% auto;
            padding: 20px;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-title {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.5em;
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
        <h1>Faculty Management</h1>
        
        <div class="form-container">
            <h2>Add New Faculty Member</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="faculty_id">Faculty ID</label>
                    <input type="text" id="faculty_id" name="faculty_id" required>
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" name="lname" required>
                </div>
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" name="fname" required>
                </div>
                <div class="form-group">
                    <label for="mi">Middle Initial</label>
                    <input type="text" id="mi" name="mi" maxlength="1">
                </div>
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Add Faculty Member</button>
            </form>
        </div>

        <h2>Faculty List</h2>
        <table>
            <thead>
                <tr>
                    <th>Faculty ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faculty as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['faculty_id']); ?></td>
                        <td><?php echo htmlspecialchars($member['fname'] . ' ' . $member['mi'] . '. ' . $member['lname']); ?></td>
                        <td><?php echo htmlspecialchars($member['course_name'] ?? 'Not Assigned'); ?></td>
                        <td>
                            <button class="edit-btn" onclick="editFaculty(<?php echo htmlspecialchars(json_encode($member)); ?>)">Edit</button>
                            <button class="delete-btn" onclick="deleteFaculty(<?php echo $member['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Edit Faculty Member</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_faculty_id">Faculty ID</label>
                    <input type="text" id="edit_faculty_id" name="faculty_id" required>
                </div>
                <div class="form-group">
                    <label for="edit_lname">Last Name</label>
                    <input type="text" id="edit_lname" name="lname" required>
                </div>
                <div class="form-group">
                    <label for="edit_fname">First Name</label>
                    <input type="text" id="edit_fname" name="fname" required>
                </div>
                <div class="form-group">
                    <label for="edit_mi">Middle Initial</label>
                    <input type="text" id="edit_mi" name="mi" maxlength="1">
                </div>
                <div class="form-group">
                    <label for="edit_course_id">Course</label>
                    <select id="edit_course_id" name="course_id">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Update Faculty Member</button>
            </form>
        </div>
    </div>

    <script>
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

        function editFaculty(member) {
            // Populate the form with faculty data
            document.getElementById('edit_id').value = member.id;
            document.getElementById('edit_faculty_id').value = member.faculty_id;
            document.getElementById('edit_lname').value = member.lname;
            document.getElementById('edit_fname').value = member.fname;
            document.getElementById('edit_mi').value = member.mi;
            document.getElementById('edit_course_id').value = member.course_id || '';

            // Display the modal
            modal.style.display = "block";
        }

        function deleteFaculty(id) {
            if (confirm('Are you sure you want to delete this faculty member?')) {
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