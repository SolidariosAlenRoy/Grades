<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header('Location: ../login.php');
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build courses query
$query = "SELECT c.course_id, c.course_name, c.schedule, c.room, 
          COUNT(DISTINCT e.student_id) as total_students,
          COUNT(DISTINCT g.grade_id) as grades_submitted
          FROM courses c 
          LEFT JOIN enrollments e ON c.course_id = e.course_id 
          LEFT JOIN grades g ON c.course_id = g.course_id 
          WHERE c.faculty_id = ?";

if ($search) {
    $query .= " AND (c.course_name LIKE ? OR c.schedule LIKE ?)";
}

$query .= " GROUP BY c.course_id ORDER BY c.course_name";

$stmt = $conn->prepare($query);

if ($search) {
    $search_param = "%$search%";
    $stmt->bind_param("iss", $faculty_id, $search_param, $search_param);
} else {
    $stmt->bind_param("i", $faculty_id);
}

$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Solidarios</title>
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
                <li>
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
                <li class="active">
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
                    <h1>My Courses</h1>
                    <div class="user-info">
                        <span>Faculty ID: <?php echo htmlspecialchars($faculty_id); ?></span>
                    </div>
                </div>
            </header>

            <div class="content-section">
                <div class="filters">
                    <form method="GET" class="search-form">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Search courses" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>

                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                <span class="course-id"><?php echo htmlspecialchars($course['course_id']); ?></span>
                            </div>
                            <div class="course-details">
                                <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['schedule']); ?></p>
                                <p><i class="fas fa-door-open"></i> <?php echo htmlspecialchars($course['room']); ?></p>
                                <p><i class="fas fa-users"></i> <?php echo $course['total_students']; ?> Students</p>
                                <p><i class="fas fa-check-circle"></i> <?php echo $course['grades_submitted']; ?> Grades Submitted</p>
                            </div>
                            <div class="course-actions">
                                <a href="course_students.php?course=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                                <a href="grades.php?course=<?php echo $course['course_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Manage Grades
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html> 