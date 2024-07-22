<!-- delete_course.php -->
<?php
require_once '../config/database.php';

// เริ่มต้น session
session_start();

// ตรวจสอบ CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET['token']) || !hash_equals($_SESSION['token'], $_GET['token']))) {
    $_SESSION['error_message'] = "Invalid CSRF token.";
    header("Location: manage_courses.php");
    exit();
}

// ตรวจสอบและกรองค่า id
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error_message'] = "Invalid course ID.";
    header("Location: manage_courses.php");
    exit();
}

try {
    // เริ่มต้น transaction
    $pdo->beginTransaction();

    // ลบข้อมูล user_videos ที่เกี่ยวข้องกับคอร์สนี้
    $stmt = $pdo->prepare("DELETE uv FROM user_videos uv
                           JOIN videos v ON uv.video_id = v.id
                           WHERE v.course_id = ?");
    $stmt->execute([$id]);

    // ลบข้อมูลบทเรียนย่อยที่เกี่ยวข้องกับคอร์สนี้ก่อน
    $stmt = $pdo->prepare("DELETE FROM chapters WHERE course_id = ?");
    $stmt->execute([$id]);

    // ลบข้อมูลวิดีโอที่เกี่ยวข้องกับคอร์สนี้
    $stmt = $pdo->prepare("DELETE FROM videos WHERE course_id = ?");
    $stmt->execute([$id]);

    // ลบข้อมูล user_courses ที่เกี่ยวข้องกับคอร์สนี้
    $stmt = $pdo->prepare("DELETE FROM user_courses WHERE course_id = ?");
    $stmt->execute([$id]);

    // ลบข้อมูลคอร์ส
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    if ($stmt->execute([$id])) {
        // ถ้าทุกอย่างสำเร็จ ให้ commit transaction
        $pdo->commit();
        $_SESSION['success_message'] = "Course deleted successfully";
    } else {
        // ถ้ามีข้อผิดพลาด ให้ rollback transaction
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error deleting course";
    }
} catch (Exception $e) {
    // ถ้ามีข้อผิดพลาด ให้ rollback transaction และแสดงข้อความ error
    $pdo->rollBack();
    $_SESSION['error_message'] = "Failed to delete course: " . $e->getMessage();
}

header("Location: manage_courses.php");
exit();
?>
