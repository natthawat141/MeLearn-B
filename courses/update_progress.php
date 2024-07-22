<!-- update_progress.php -->
<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['video_id']) || !isset($_POST['course_id'])) {
    http_response_code(400);
    exit();
}

$user_id = $_SESSION['user_id'];
$video_id = $_POST['video_id'];
$course_id = $_POST['course_id'];

// อัปเดตสถานะการดูวิดีโอ
$stmt = $pdo->prepare("INSERT INTO user_videos (user_id, video_id, watched) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE watched = 1");
$stmt->execute([$user_id, $video_id]);

// ดึงจำนวนวิดีโอทั้งหมดในคอร์สนี้
$stmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE course_id = ?");
$stmt->execute([$course_id]);
$total_videos = $stmt->fetchColumn();

// ดึงจำนวนวิดีโอที่ผู้ใช้ดูในคอร์สนี้
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_videos WHERE user_id = ? AND video_id IN (SELECT id FROM videos WHERE course_id = ?) AND watched = 1");
$stmt->execute([$user_id, $course_id]);
$watched_videos = $stmt->fetchColumn();

// คำนวณความคืบหน้าจากการดูวิดีโอเท่านั้น
$video_progress = ($total_videos > 0) ? ($watched_videos / $total_videos) * 100 : 0;

// อัปเดตความคืบหน้าในตาราง user_courses
$stmt = $pdo->prepare("UPDATE user_courses SET progress = ? WHERE user_id = ? AND course_id = ?");
$stmt->execute([$video_progress, $user_id, $course_id]);

http_response_code(200);
echo json_encode(["success" => true, "progress" => min($video_progress, 100)]);
?>