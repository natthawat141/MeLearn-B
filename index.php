<!-- index.php -->
<?php
echo"hello";
session_start();
require_once 'config/database.php';
include 'user/includes/user_header.php';

// ดึงข้อมูลคอร์สทั้งหมดจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT id, title, description, thumbnail FROM courses ORDER BY id DESC LIMIT 8");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>Welcome to MeLearn</h2>
    <p>Your platform for online learning and development.</p>

    <h3>Our Courses</h3>
    <div class="row">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-md-3 d-flex align-items-stretch">
                    <div class="card mb-4">
                        <img src="uploads/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="card-img-top">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars(mb_strimwidth($course['description'], 0, 100, "..."))); ?></p>
                            <a href="user/course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary mt-auto">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No courses available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'user/includes/user_footer.php'; ?>
