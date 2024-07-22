<!-- manage_chapters.php -->

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

$token = $_SESSION['token'];

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
if ($course_id) {
    $stmt = $pdo->prepare("SELECT * FROM chapters WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $chapters = $stmt->fetchAll();
} else {
    $chapters = [];
}
?>

<div class="container">
    <h2>Manage Chapters</h2>
    <?php if ($course_id): ?>
        <a href="add_chapter.php?course_id=<?php echo htmlspecialchars($course_id); ?>" class="btn btn-primary">Add New Chapter</a>
    <?php else: ?>
        <a href="add_chapter.php" class="btn btn-primary">Add New Chapter</a>
    <?php endif; ?>
    <table class="table table-striped mt-4 manage-chapters-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chapters as $chapter): ?>
            <tr>
                <td><?php echo htmlspecialchars($chapter['id']); ?></td>
                <td><?php echo htmlspecialchars($chapter['title']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($chapter['description'])); ?></td>
                <td><?php echo htmlspecialchars($chapter['order_number']); ?></td>
                <td>
                    <a href="edit_chapter.php?id=<?php echo htmlspecialchars($chapter['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_chapter.php?id=<?php echo htmlspecialchars($chapter['id']); ?>&token=<?php echo htmlspecialchars($token); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>

