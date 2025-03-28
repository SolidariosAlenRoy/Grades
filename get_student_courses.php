<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if(isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
    
    try {
        $stmt = $conn->prepare("SELECT course_id FROM student_course WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $courseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($courseIds);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID is required']);
}
?> 