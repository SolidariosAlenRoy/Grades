<?php
session_start();
require_once 'config/database.php';



$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $student_id = $_POST['student_id'] ?? '';
    $admin_id = $_POST['admin_id'] ?? '';

    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                try {
                    $stmt = $conn->prepare("INSERT INTO users (email, password, user_type, student_id, admin_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$email, $hashed_password, $user_type, $student_id, $admin_id]);
                    $success = "User account created successfully!";
                } catch (PDOException $e) {
                    $error = "Failed to create user account. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User Account - Admin Panel</title>
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

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-container h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
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
            width: 100%;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background-color: #dcfce7;
            border: 1px solid #22c55e;
            color: #16a34a;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .id-fields {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .id-fields.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .form-container {
                margin: 20px;
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
            <h1>Create User Account</h1>
            
            <div class="form-container">
                <h2>New User Registration</h2>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Select user type</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div id="student_id_field" class="form-group id-fields">
                        <label for="student_id">Student ID (2 digits)</label>
                        <input type="text" id="student_id" name="student_id" pattern="[0-9]{2}" maxlength="2">
                    </div>

                    <div id="admin_id_field" class="form-group id-fields">
                        <label for="admin_id">Admin ID (1 digit)</label>
                        <input type="text" id="admin_id" name="admin_id" pattern="[0-9]{1}" maxlength="1">
                    </div>

                    <button type="submit" class="btn">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('user_type').addEventListener('change', function() {
            const studentIdField = document.getElementById('student_id_field');
            const adminIdField = document.getElementById('admin_id_field');
            const userType = this.value;
            
            // Hide all ID fields first
            studentIdField.classList.remove('show');
            adminIdField.classList.remove('show');
            
            // Show relevant ID field based on user type
            if (userType === 'student') {
                studentIdField.classList.add('show');
            } else if (userType === 'admin') {
                adminIdField.classList.add('show');
            }
        });
    </script>
</body>
</html> 