
<!-- manage_courses.php -->
<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

// เริ่มต้น session หากยังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

try {
    // Fetch all courses with chapter and video counts
    $stmt = $pdo->prepare("SELECT courses.id, courses.title, 
                           COUNT(DISTINCT chapters.id) as chapter_count, 
                           COUNT(DISTINCT videos.id) as video_count, 
                           COUNT(DISTINCT user_courses.user_id) as student_count 
                           FROM courses 
                           LEFT JOIN chapters ON courses.id = chapters.course_id 
                           LEFT JOIN videos ON courses.id = videos.course_id 
                           LEFT JOIN user_courses ON courses.id = user_courses.course_id 
                           GROUP BY courses.id");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching courses: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit();
}
?>

<div class="container mt-5">
    <h2>Manage Courses</h2>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <a href="add_course.php" class="btn btn-primary mb-3">Add New Course</a>
    <table class="table table-striped mt-3 manage-courses-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Course Title</th>
                <th>Chapters</th>
                <th>Videos</th>
                <th>Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($course['chapter_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($course['video_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($course['student_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <a href="edit_course.php?id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="manage_chapters.php?course_id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-info btn-sm">Manage Chapters</a>
                    <a href="manage_videos.php?course_id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-info btn-sm">Manage Videos</a>
                    <a href="manage_exercises.php?course_id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-info btn-sm">Manage Exercises</a>
                    <a href="delete_course.php?id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>&token=<?php echo htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>
