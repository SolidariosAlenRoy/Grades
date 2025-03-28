<?php
require_once 'config/database.php';

if (isset($_GET['faculty_id'])) {
    $stmt = $conn->prepare("SELECT course_id FROM faculty_course WHERE faculty_id = ?");
    $stmt->execute([$_GET['faculty_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: application/json');
    echo json_encode($courses);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Faculty ID not provided']);
} 