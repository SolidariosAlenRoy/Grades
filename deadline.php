<?php
session_start();
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'set_deadline') {
            $_SESSION['deadline'] = $_POST['deadline'];
            // Store deadline in database
            $stmt = $conn->prepare("INSERT INTO deadlines (deadline_date, created_at) VALUES (?, NOW())");
            $stmt->execute([$_POST['deadline']]);
        } elseif ($_POST['action'] === 'clear_deadline') {
            unset($_SESSION['deadline']);
            // Clear deadline from database
            $stmt = $conn->prepare("DELETE FROM deadlines ORDER BY created_at DESC LIMIT 1");
            $stmt->execute();
        }
    }
}

// Get current deadline from database
$stmt = $conn->query("SELECT deadline_date FROM deadlines ORDER BY created_at DESC LIMIT 1");
$deadline = $stmt->fetch(PDO::FETCH_ASSOC);
$deadline = $deadline ? $deadline['deadline_date'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Submission Deadline</title>
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

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
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

        .btn-danger {
            background-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .countdown-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .countdown {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .countdown.expired {
            color: #c0392b;
        }

        .deadline-info {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .form-container, .countdown-container {
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
                <a href="grades.php">Grades</a>
            </div>
            <div class="menu-item">
                <a href="gwa.php">GWA</a>
            </div>
            <div class="menu-item active">
                <a href="deadline.php">Deadline</a>
            </div>
        </div>

        <div class="content">
            <h1>Grade Submission Deadline</h1>
            
            <div class="form-container">
                <h2>Set Grade Submission Deadline</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="set_deadline">
                    <div class="form-group">
                        <label for="deadline">Select Deadline:</label>
                        <input type="datetime-local" id="deadline" name="deadline" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn">Set Deadline</button>
                    </div>
                </form>
            </div>

            <?php if ($deadline): ?>
                <div class="countdown-container">
                    <h2>Time Remaining</h2>
                    <div class="countdown" id="countdown"></div>
                    <div class="deadline-info">
                        Deadline: <?php echo date('F j, Y g:i A', strtotime($deadline)); ?>
                    </div>
                    <div class="button-group">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="clear_deadline">
                            <button type="submit" class="btn btn-danger">Clear Deadline</button>
                        </form>
                    </div>
                </div>

                <script>
                    function updateCountdown() {
                        let deadline = new Date("<?php echo $deadline; ?>").getTime();
                        let now = new Date().getTime();
                        let timeLeft = deadline - now;

                        const countdownElement = document.getElementById("countdown");
                        
                        if (timeLeft > 0) {
                            let days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                            let hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            let minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                            let seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                            
                            countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                            countdownElement.classList.remove('expired');
                        } else {
                            countdownElement.innerHTML = "Deadline has passed!";
                            countdownElement.classList.add('expired');
                        }
                    }

                    // Update countdown every second
                    setInterval(updateCountdown, 1000);
                    updateCountdown();
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 