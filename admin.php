<?php
require_once 'config/database.php';

// Initialize message variables
$success_msg = $error_msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);
                $email = trim($_POST['email']);
                $fullname = trim($_POST['fullname']);

                // Validate input
                if (empty($username) || empty($password) || empty($email) || empty($fullname)) {
                    $error_msg = "All fields are required.";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    try {
                        $stmt = $conn->prepare("INSERT INTO admin (username, password, email, fullname) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $hashed_password, $email, $fullname]);
                        $success_msg = "Admin user added successfully.";
                    } catch (PDOException $e) {
                        $error_msg = "Error adding admin user. Username may already exist.";
                    }
                }
                break;

            case 'edit':
                $admin_id = $_POST['admin_id'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $fullname = trim($_POST['fullname']);
                $password = trim($_POST['password']);

                try {
                    if (!empty($password)) {
                        // Update with new password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ?, fullname = ?, password = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $fullname, $hashed_password, $admin_id]);
                    } else {
                        // Update without changing password
                        $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ?, fullname = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $fullname, $admin_id]);
                    }
                    $success_msg = "Admin user updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Error updating admin user.";
                }
                break;

            case 'delete':
                $admin_id = $_POST['admin_id'];
                try {
                    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $success_msg = "Admin user deleted successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Error deleting admin user.";
                }
                break;
        }
    }
}

// Fetch all admin users
$stmt = $conn->query("SELECT * FROM admin ORDER BY username");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Student Information System</title>
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

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .admin-table {
            width: 100%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .admin-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .admin-table tr:hover {
            background-color: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        @media (max-width: 1200px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
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
            <div class="menu-item active">
                <a href="admin.php">Admin Users</a>
            </div>
            <div class="menu-item">
                <a href="create_user.php">Create Account</a>
            </div>
            <div class="menu-item">
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="content">
            <h1>Admin Management</h1>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="admin-grid">
                <div class="form-container">
                    <h2>Add New Admin</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Admin</button>
                    </form>
                </div>

                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['fullname']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="editAdmin(<?php echo $admin['id']; ?>)">Edit</button>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this admin user?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editAdmin(adminId) {
            // Fetch admin details and populate edit form
            // You can implement this using AJAX or a modal form
            alert('Edit functionality to be implemented');
        }
    </script>
</body>
</html> 