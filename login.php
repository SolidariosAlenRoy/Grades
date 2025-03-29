<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if it's a Gmail account (case-insensitive)
        $is_gmail = stripos($email, '@gmail.com') !== false;
        
        // Debug information
        error_log("Login attempt - Email: " . $email . ", Is Gmail: " . ($is_gmail ? "Yes" : "No"));
        
        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email'] = $user['email'];

            // If it's a Gmail account, get faculty information
            if ($is_gmail) {
                $stmt = $conn->prepare("SELECT id FROM faculty WHERE email = ?");
                $stmt->execute([$email]);
                $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($faculty) {
                    $_SESSION['faculty_id'] = $faculty['id'];
                } else {
                    // If faculty doesn't exist, create a new faculty record
                    $stmt = $conn->prepare("INSERT INTO faculty (email) VALUES (?)");
                    $stmt->execute([$email]);
                    $_SESSION['faculty_id'] = $conn->lastInsertId();
                }
            }

            // Debug information
            error_log("User authenticated - Type: " . $user['user_type'] . ", Is Gmail: " . ($is_gmail ? "Yes" : "No"));

            // Redirect based on email type and user type
            if ($is_gmail) {
                // If Gmail account, redirect to faculty dashboard
                error_log("Redirecting to faculty dashboard");
                header("Location: facultyindex.php");
                exit();
            } else {
                // For non-Gmail accounts, check user type
                switch ($user['user_type']) {
                    case 'admin':
                        header("Location: index.php");
                        break;
                    case 'student':
                        header("Location: index.php");
                        break;
                    default:
                        header("Location: index.php");
                }
                exit();
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management System</title>
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

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-header p {
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

        .btn-login {
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

        .btn-login:hover {
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

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .register-link a {
            color: #3498db;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .login-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 4px;
            font-size: 13px;
            color: #64748b;
        }

        .login-info ul {
            list-style: none;
            padding-left: 0;
        }

        .login-info li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Student Management System</h1>
            <p>Please login to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="example@gmail.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="login-info">
            <p>Login Information:</p>
            <ul>
                <li>• Admin: Use non-Gmail account</li>
                <li>• Faculty: Use Gmail account (e.g., example@gmail.com)</li>
                <li>• Student: Use non-Gmail account</li>
            </ul>
        </div>
    </div>
</body>
</html> 