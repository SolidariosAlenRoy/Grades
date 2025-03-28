<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header('Location: ../login.php');
    exit();
}

$faculty_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Solidarios</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">
                <h2>Solidarios</h2>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <a href="facultyindex.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="students.php">
                        <i class="fas fa-users"></i>
                        <span>Student List</span>
                    </a>
                </li>
                <li>
                    <a href="faculty_list.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Faculty List</span>
                    </a>
                </li>
                <li>
                    <a href="courses.php">
                        <i class="fas fa-book"></i>
                        <span>My Courses</span>
                    </a>
                </li>
                <li>
                    <a href="grades.php">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Grades</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <div class="header-content">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                    <div class="user-info">
                        <span>Faculty ID: <?php echo htmlspecialchars($faculty_id); ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-stats">
                <?php
                // Get statistics
                $query = "SELECT 
                    (SELECT COUNT(*) FROM students) as total_students,
                    (SELECT COUNT(*) FROM courses WHERE faculty_id = ?) as total_courses";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $faculty_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $stats = $result->fetch_assoc();
                ?>

                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <p><?php echo $stats['total_students']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-info">
                        <h3>My Courses</h3>
                        <p><?php echo $stats['total_courses']; ?></p>
                    </div>
                </div>
            </div>

            <div class="recent-activities">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php
                    // Get recent grade submissions
                    $query = "SELECT g.*, s.name as student_name, c.course_name 
                             FROM grades g 
                             JOIN students s ON g.student_id = s.student_id 
                             JOIN courses c ON g.course_id = c.course_id 
                             WHERE c.faculty_id = ? 
                             ORDER BY g.updated_at DESC LIMIT 5";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $faculty_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($activity = $result->fetch_assoc()) {
                        echo "<div class='activity-item'>";
                        echo "<i class='fas fa-edit'></i>";
                        echo "<div class='activity-info'>";
                        echo "<p>Updated grade for " . htmlspecialchars($activity['student_name']) . "</p>";
                        echo "<small>" . htmlspecialchars($activity['course_name']) . " - " . date('M d, Y', strtotime($activity['updated_at'])) . "</small>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
