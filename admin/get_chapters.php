<!-- get_chapters.php -->

<?php
require_once '../config/database.php';

// ตรวจสอบว่ามีการระบุ course_id และตรวจสอบความถูกต้อง
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if ($course_id === null || $course_id === false) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid course ID."]);
    exit();
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id, title FROM chapters WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีบทในคอร์สหรือไม่
    if ($chapters === false) {
        http_response_code(404);
        echo json_encode(["error" => "No chapters found for the specified course ID."]);
        exit();
    }

    echo json_encode($chapters);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>
