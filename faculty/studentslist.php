<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header('Location: ../login.php');
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';

// Get all courses for filter
$courses_query = "SELECT DISTINCT c.course_id, c.course_name 
                  FROM courses c 
                  WHERE c.faculty_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

// Build student query
$query = "SELECT s.student_id, s.name, s.email, c.course_name 
          FROM students s 
          LEFT JOIN enrollments e ON s.student_id = e.student_id 
          LEFT JOIN courses c ON e.course_id = c.course_id 
          WHERE c.faculty_id = ?";

if ($search) {
    $query .= " AND (s.student_id LIKE ? OR s.name LIKE ?)";
}

if ($course_filter) {
    $query .= " AND c.course_id = ?";
}

$query .= " ORDER BY s.name";

$stmt = $conn->prepare($query);

if ($search && $course_filter) {
    $search_param = "%$search%";
    $stmt->bind_param("issi", $faculty_id, $search_param, $search_param, $course_filter);
} elseif ($search) {
    $search_param = "%$search%";
    $stmt->bind_param("iss", $faculty_id, $search_param, $search_param);
} elseif ($course_filter) {
    $stmt->bind_param("ii", $faculty_id, $course_filter);
} else {
    $stmt->bind_param("i", $faculty_id);
}

$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - Solidarios</title>
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
                <li class="active">
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
                    <h1>Student List</h1>
                    <div class="user-info">
                        <span>Faculty ID: <?php echo htmlspecialchars($faculty_id); ?></span>
                    </div>
                </div>
            </header>

            <div class="content-section">
                <div class="filters">
                    <form method="GET" class="search-form">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Search by ID or Name" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                        <select name="course" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                    <td>
                                        <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="grades.php?student=<?php echo $student['student_id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html> 