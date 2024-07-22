<!-- delete_video.php -->

<?php
require_once '../config/database.php';

// ตรวจสอบสถานะของ session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบ CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET['token']) || !hash_equals($_SESSION['token'], $_GET['token']))) {
    echo "Invalid CSRF token.";
    exit();
}

// ตรวจสอบและกรองค่า id และ course_id
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);

if (!$id || !$course_id) {
    echo "Invalid video or course ID.";
    exit();
}

try {
    // Fetch video details to get the file path
    $stmt = $pdo->prepare("SELECT video_url FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch();

    if ($video) {
        // Delete the video file
        $file_path = '../' . ltrim($video['video_url'], '/');
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete the video record from the database
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "Video deleted successfully";
        } else {
            echo "Error deleting video";
        }
    } else {
        echo "Video not found";
    }
} catch (Exception $e) {
    echo "Failed to delete video: " . $e->getMessage();
}

header("Location: manage_videos.php?course_id=" . htmlspecialchars($course_id));
exit();
?>
