<?php
require_once 'config/database.php';

// Get counts for dashboard
$stmt = $conn->query("SELECT COUNT(*) as count FROM student");
$studentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM faculty");
$facultyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM course");
$courseCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Add FullCalendar CDN -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .calendar-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .calendar-container h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #calendar {
            height: auto;
            background: white;
            border-radius: 8px;
            padding: 0;
            overflow: hidden;
        }

        /* FullCalendar Custom Styles */
        .fc {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 100%;
            height: 100%;
        }

        .fc .fc-toolbar {
            padding: 0;
            margin-bottom: 1em;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25em !important;
            font-weight: 600 !important;
            color: #2c3e50;
        }

        .fc .fc-button {
            background: transparent !important;
            border: none !important;
            color: #666 !important;
            padding: 5px 8px !important;
            font-size: 18px !important;
            box-shadow: none !important;
        }

        .fc .fc-button:hover {
            background: #f8f9fa !important;
            color: #333 !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #f0f0f0 !important;
            color: #333 !important;
        }

        .fc .fc-button-primary:disabled {
            background: #f0f0f0 !important;
            color: #999 !important;
            opacity: 0.6;
        }

        .fc table {
            border: none;
        }

        .fc th {
            padding: 8px 0;
            font-weight: 500;
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            border: none;
        }

        .fc .fc-scrollgrid {
            border: none !important;
        }

        .fc .fc-daygrid-day {
            min-height: 60px !important;
            border: none !important;
        }

        .fc .fc-daygrid-day-frame {
            padding: 4px !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.9em;
            padding: 4px;
            color: #333;
        }

        .fc .fc-day-today {
            background: transparent !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            color: #fff;
            background-color: #9c27b0;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fc .fc-day-other .fc-daygrid-day-number {
            color: #ccc;
        }

        .fc .fc-daygrid-day-events {
            margin-top: 2px;
        }

        .fc .fc-event {
            background: #f8f9fa;
            border: none;
            margin: 1px 0;
            padding: 2px 4px;
            font-size: 0.85em;
            border-radius: 4px;
            cursor: pointer;
        }

        .fc .fc-daygrid-event-harness {
            margin: 1px 0;
        }

        .fc .fc-daygrid-more-link {
            color: #666;
            font-size: 0.85em;
        }

        .fc .fc-day-sat,
        .fc .fc-day-sun {
            background-color: #fafafa;
        }

        .notification-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .notification-container h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notification-list {
            list-style: none;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #e3f2fd;
        }

        .notification-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .notification-message {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .notification-time {
            color: #999;
            font-size: 12px;
        }

        .notification-badge {
            background-color: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 10px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .fc .fc-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .fc .fc-toolbar-title {
                text-align: center;
                margin-bottom: 10px;
            }

            .fc .fc-button-group {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 200px;
            }

            .fc .fc-toolbar {
                flex-direction: row;
                gap: 8px;
            }

            .fc .fc-toolbar-title {
                font-size: 1.1em !important;
            }

            .fc .fc-button {
                padding: 4px 6px !important;
                font-size: 16px !important;
            }

            .fc th {
                font-size: 0.8em;
            }

            .fc .fc-daygrid-day-number {
                font-size: 0.85em;
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
            <div class="menu-item active">
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
            <h1>Dashboard</h1>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="number"><?php echo $studentCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Faculty</h3>
                    <div class="number"><?php echo $facultyCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Courses</h3>
                    <div class="number"><?php echo $courseCount; ?></div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="calendar-container">
                    <h3>Academic Calendar</h3>
                    <div id="calendar"></div>
                </div>
                <div class="notification-container">
                    <h3>Notifications <span class="notification-badge">3</span></h3>
                    <ul class="notification-list">
                        <li class="notification-item unread">
                            <div class="notification-title">Grade Submission Deadline</div>
                            <div class="notification-message">Deadline for submitting grades is approaching on April 15, 2024.</div>
                            <div class="notification-time">2 hours ago</div>
                        </li>
                        <li class="notification-item unread">
                            <div class="notification-title">New Faculty Member</div>
                            <div class="notification-message">A new faculty member has been added to the system.</div>
                            <div class="notification-time">1 day ago</div>
                        </li>
                        <li class="notification-item">
                            <div class="notification-title">System Update</div>
                            <div class="notification-message">The system has been updated with new features.</div>
                            <div class="notification-time">3 days ago</div>
                        </li>
                        <li class="notification-item">
                            <div class="notification-title">Faculty Meeting</div>
                            <div class="notification-message">Monthly faculty meeting scheduled for April 20, 2024.</div>
                            <div class="notification-time">1 week ago</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next'
                },
                titleFormat: { year: 'numeric', month: 'long' },
                events: [
                    {
                        title: 'Grade Submission Deadline',
                        start: '2024-04-15',
                        color: '#e74c3c',
                        textColor: '#ffffff',
                        description: 'Final deadline for submitting all grades'
                    },
                    {
                        title: 'Faculty Meeting',
                        start: '2024-04-20',
                        color: '#3498db',
                        textColor: '#ffffff',
                        description: 'Monthly faculty meeting'
                    },
                    {
                        title: 'Student Registration',
                        start: '2024-04-25',
                        color: '#2ecc71',
                        textColor: '#ffffff',
                        description: 'New student registration period'
                    },
                    {
                        title: 'Midterm Exams',
                        start: '2024-05-01',
                        color: '#f1c40f',
                        textColor: '#2c3e50',
                        description: 'Midterm examination period'
                    },
                    {
                        title: 'Final Exams',
                        start: '2024-05-15',
                        color: '#9b59b6',
                        textColor: '#ffffff',
                        description: 'Final examination period'
                    }
                ],
                eventClick: function(info) {
                    const event = info.event;
                    const description = event.extendedProps.description || 'No description available';
                    alert(`${event.title}\n\n${description}`);
                },
                height: 'auto',
                aspectRatio: 1.5,
                fixedWeekCount: false,
                showNonCurrentDates: true,
                dayMaxEvents: true,
                editable: false,
                selectable: false,
                weekends: true,
                firstDay: 0, // Start week on Sunday
                displayEventTime: false,
                eventDisplay: 'block',
                eventInteractive: true,
                dayMaxEventRows: 2,
                moreLinkClick: 'popover'
            });
            calendar.render();
        });
    </script>
</body>
</html> 