<!-- manage_videos.php -->

<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if ($course_id) {
    // Fetch course title
    $stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    // Fetch videos for the course with chapter information
    $stmt = $pdo->prepare("
        SELECT videos.*, chapters.title as chapter_title 
        FROM videos 
        LEFT JOIN chapters ON videos.chapter_id = chapters.id 
        WHERE videos.course_id = ?
    ");
    $stmt->execute([$course_id]);
    $videos = $stmt->fetchAll();
} else {
    header("Location: manage_courses.php");
    exit();
}

// ตรวจสอบสถานะของ session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>

<div class="container mt-5">
    <h2>Manage Videos for Course: <?php echo htmlspecialchars($course['title']); ?></h2>
    <a href="upload_video.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary mb-3">Add New Video</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Order</th>
                <th>Chapter</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $video): ?>
            <tr>
                <td><?php echo $video['id']; ?></td>
                <td><?php echo htmlspecialchars($video['title']); ?></td>
                <td><?php echo htmlspecialchars($video['description']); ?></td>
                <td><?php echo $video['order_number']; ?></td>
                <td><?php echo htmlspecialchars($video['chapter_title']); ?></td>
                <td>
                    <a href="edit_video.php?id=<?php echo $video['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form action="delete_video.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this video?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>
