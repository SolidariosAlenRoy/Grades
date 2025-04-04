<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Determine user type based on username format
        $user_type = '';
        if (strlen($username) === 2 && is_numeric($username)) {
            $user_type = 'student';
        } elseif (strlen($username) === 1 && is_numeric($username)) {
            $user_type = 'admin';
        } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user_type = 'faculty';
        } else {
            $error = "Invalid username format. Please check the registration information below.";
        }

        if (!$error) {
            // Check if username already exists
            if ($user_type === 'student') {
                $stmt = $conn->prepare("SELECT id FROM student WHERE student_id = ?");
            } elseif ($user_type === 'faculty') {
                $stmt = $conn->prepare("SELECT id FROM faculty WHERE email = ?");
            } else {
                $stmt = $conn->prepare("SELECT id FROM admin WHERE admin_id = ?");
            }
            
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                try {
                    if ($user_type === 'student') {
                        $stmt = $conn->prepare("INSERT INTO student (student_id, password) VALUES (?, ?)");
                        $stmt->execute([$username, $hashed_password]);
                    } elseif ($user_type === 'faculty') {
                        $stmt = $conn->prepare("INSERT INTO faculty (email, password) VALUES (?, ?)");
                        $stmt->execute([$username, $hashed_password]);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO admin (admin_id, password) VALUES (?, ?)");
                        $stmt->execute([$username, $hashed_password]);
                    }
                    $success = "Registration successful! Please login.";
                } catch (PDOException $e) {
                    $error = "Registration failed. Please try again.";
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
    <title>Register - Student Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .register-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .register-header p {
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn-register {
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

        .btn-register:hover {
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

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .register-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 4px;
            font-size: 13px;
            color: #64748b;
        }

        .register-info ul {
            list-style: none;
            padding-left: 0;
        }

        .register-info li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Please fill in the details to register</p>
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
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-register">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>

        
    </div>
</body>
</html> 