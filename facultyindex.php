<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="css/faculty.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Faculty Portal</h2>
            </div>
            <nav>
                <div class="accordion">
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Dashboard</span>
                            <span class="arrow">▼</span>
                        </div>
                        <div class="accordion-content">
                            <ul>
                                <li><a href="#" class="active" data-page="dashboard">Home</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Students</span>
                            <span class="arrow">▼</span>
                        </div>
                        <div class="accordion-content">
                            <ul>
                                <li><a href="#" data-page="students">View Student List</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Faculty</span>
                            <span class="arrow">▼</span>
                        </div>
                        <div class="accordion-content">
                            <ul>
                                <li><a href="#" data-page="faculty">View Faculty List</a></li>
                                <li><a href="#" data-page="courses">My Courses</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div class="header-content">
                    <h1>Welcome, Faculty</h1>
                    <div class="user-info">
                        <span id="facultyName">Loading...</span>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Dashboard Content -->
                <div id="dashboard" class="page-content active">
                    <h2>Dashboard Overview</h2>
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <h3>Total Students</h3>
                            <p id="totalStudents">Loading...</p>
                        </div>
                        <div class="stat-card">
                            <h3>My Courses</h3>
                            <p id="totalCourses">Loading...</p>
                        </div>
                    </div>
                </div>

                <!-- Students List Content -->
                <div id="students" class="page-content">
                    <h2>Student List</h2>
                    <table id="studentsTable" class="display">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Faculty List Content -->
                <div id="faculty" class="page-content">
                    <h2>Faculty List</h2>
                    <table id="facultyTable" class="display">
                        <thead>
                            <tr>
                                <th>Faculty ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Faculty Courses Content -->
                <div id="courses" class="page-content">
                    <h2>My Courses</h2>
                    <table id="coursesTable" class="display">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Schedule</th>
                                <th>Students Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/faculty.js"></script>
</body>
</html>
