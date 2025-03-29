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
            background-color: #f5f6fa;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 14px;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn-create {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-create:hover {
            background-color: #2980b9;
        }

        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background-color: #dcfce7;
            color: #16a34a;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .id-fields {
            display: none;
        }

        .id-fields.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create User Account</h1>
            <p>Admin panel for creating new user accounts</p>
        </div>

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

            <button type="submit" class="btn-create">Create Account</button>
        </form>

        <a href="index.php" class="back-link">← Back to Dashboard</a>
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