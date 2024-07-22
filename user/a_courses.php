<!-- a_courses.php -->
<?php
session_start();
require_once '../config/database.php';

// สร้าง CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ดึงข้อมูลคอร์สทั้งหมดจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT id, title, description, thumbnail FROM courses LIMIT 4");
$stmt->execute();
$all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>คอร์สทั้งหมด</h2>
    
    <?php if (count($all_courses) > 0): ?>
        <div class="row mt-4">
        <?php foreach ($all_courses as $course): ?>
            <div class="col-md-3 d-flex align-items-stretch">
                <div class="card mb-4">
                    <img class="card-img-top" src="/me/admin/<?php echo htmlspecialchars($course['thumbnail'], ENT_QUOTES, 'UTF-8'); ?>" alt="Course Image">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(mb_strimwidth($course['description'], 0, 100, "..."), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="../user/course_detail.php?id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary mt-auto">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="../courses/all_courses.php" class="text-h">ดูคอร์สทั้งหมด</a>
        </div>
    <?php else: ?>
        <p class="mt-4">ไม่มีคอร์สให้แสดง</p>
    <?php endif; ?>
</div>