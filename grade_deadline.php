<?php

require_once 'config/database.php';


// Get current semester and academic year
$current_date = date('Y-m-d');
$query = "SELECT * FROM academic_periods WHERE start_date <= ? AND end_date >= ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $current_date);
$stmt->bindParam(2, $current_date);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$academic_period = $result->fetch_assoc();

// Get grade submission deadline
$deadline_query = "SELECT grade_submission_deadline FROM grade_submission_settings WHERE academic_period_id = ?";
$stmt = $conn->prepare($deadline_query);
$stmt->bind_param("i", $academic_period['id']);
$stmt->execute();
$deadline_result = $stmt->get_result();
$deadline = $deadline_result->fetch_assoc();

$grade_deadline = $deadline['grade_submission_deadline'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Submission Deadline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .deadline-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .countdown-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .time-block {
            display: inline-block;
            margin: 0 10px;
            text-align: center;
        }
        .time-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .time-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .progress {
            height: 10px;
            margin: 20px 0;
        }
        .status-badge {
            font-size: 1.1rem;
            padding: 8px 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="deadline-container">
            <h2 class="text-center mb-4">Grade Submission Deadline</h2>
            
            <?php if ($academic_period): ?>
                <div class="text-center mb-4">
                    <h4>Current Academic Period</h4>
                    <p class="lead">
                        <?php echo date('F Y', strtotime($academic_period['start_date'])); ?> - 
                        <?php echo date('F Y', strtotime($academic_period['end_date'])); ?>
                    </p>
                </div>

                <div class="countdown-box text-center">
                    <h5>Time Remaining</h5>
                    <div id="countdown">
                        <div class="time-block">
                            <div class="time-value" id="days">00</div>
                            <div class="time-label">Days</div>
                        </div>
                        <div class="time-block">
                            <div class="time-value" id="hours">00</div>
                            <div class="time-label">Hours</div>
                        </div>
                        <div class="time-block">
                            <div class="time-value" id="minutes">00</div>
                            <div class="time-label">Minutes</div>
                        </div>
                        <div class="time-block">
                            <div class="time-value" id="seconds">00</div>
                            <div class="time-label">Seconds</div>
                        </div>
                    </div>
                </div>

                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                </div>

                <div class="text-center mt-4">
                    <span class="badge status-badge" id="statusBadge">Loading...</span>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No active academic period found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Set the deadline date
        const deadline = new Date('<?php echo $grade_deadline; ?>').getTime();
        
        // Update the countdown every second
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').innerHTML = days.toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = hours.toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = seconds.toString().padStart(2, '0');

            // Calculate progress percentage
            const totalDuration = deadline - new Date('<?php echo $academic_period['start_date']; ?>').getTime();
            const progress = ((totalDuration - distance) / totalDuration) * 100;
            document.getElementById('progressBar').style.width = progress + '%';

            // Update status badge
            const statusBadge = document.getElementById('statusBadge');
            if (distance < 0) {
                clearInterval(countdown);
                statusBadge.className = 'badge bg-danger status-badge';
                statusBadge.innerHTML = 'Deadline Passed';
            } else if (days <= 7) {
                statusBadge.className = 'badge bg-warning status-badge';
                statusBadge.innerHTML = 'Urgent: Submit Grades Soon';
            } else {
                statusBadge.className = 'badge bg-success status-badge';
                statusBadge.innerHTML = 'On Track';
            }
        }, 1000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 