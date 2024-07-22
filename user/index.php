<!-- index.php -->
<?php
session_start();
require_once '../config/database.php';
include 'includes/user_header.php';

// ตรวจสอบสถานะการล็อกอินของผู้ใช้
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลคอร์สทั้งหมด
$stmt = $pdo->query("SELECT id, title, description, thumbnail FROM courses ORDER BY id DESC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="course-list-title">รายการคอร์สทั้งหมด</h2>

    <?php if (count($courses) > 0): ?>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-3 d-flex align-items-stretch">
                    <div class="card mb-4">
                        <img src="/me/admin/<?php echo htmlspecialchars($course['thumbnail'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top course-thumbnail">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars(mb_strimwidth($course['description'], 0, 100, "..."), ENT_QUOTES, 'UTF-8')); ?></p>
                            <a href="course_detail.php?id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary mt-auto">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>ไม่มีคอร์สในระบบ</p>
    <?php endif; ?>
</div>

<?php include 'includes/user_footer.php'; ?>