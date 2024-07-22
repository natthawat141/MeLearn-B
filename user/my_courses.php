<!-- my_courses.php -->
<?php
session_start();
require_once '../config/database.php';
include 'includes/user_header.php';
require 'redeem_code.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.id, c.title, c.description, c.thumbnail FROM courses c 
                       JOIN user_courses uc ON c.id = uc.course_id 
                       WHERE uc.user_id = ?");
$stmt->execute([$user_id]);
$user_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบ Redeem Code
$redeem_success = '';
$redeem_error = '';

// สร้าง CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $redeem_code = filter_input(INPUT_POST, 'redeem_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // ตรวจสอบ Redeem Code ว่าใช้งานได้หรือไม่
    list($redeem_success, $redeem_error) = redeem_code($pdo, $user_id, $redeem_code);
}
?>

<div class="container mt-5">
    <h2>คอร์สที่ลงทะเบียน</h2>
    
    <!-- ฟอร์ม Redeem Code -->
    <h4>ใช้ Redeem Code</h4>
    <?php if ($redeem_success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($redeem_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($redeem_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($redeem_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="POST" action="my_courses.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="redeem_code">Redeem Code:</label>
            <input type="text" class="form-control" id="redeem_code" name="redeem_code" required>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">ใช้ Redeem Code</button>
    </form>

    <!-- รายการคอร์สที่ลงทะเบียน -->
    <?php if (count($user_courses) > 0): ?>
        <div class="row mt-4">
        <?php foreach ($user_courses as $course): ?>
            <div class="col-md-3 d-flex align-items-stretch">
                <div class="card mb-4">
                    <img class="card-img-top" src="/me/admin/<?php echo htmlspecialchars($course['thumbnail'], ENT_QUOTES, 'UTF-8'); ?>" alt="Course Image">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars(mb_strimwidth($course['title'], 0, 100, "..."), ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(mb_strimwidth($course['description'], 0, 100, "..."), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="../courses/view.php?id=<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary mt-auto">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="mt-4">คุณยังไม่ได้ลงทะเบียนคอร์สใดๆ</p>
    <?php endif; ?>
</div>

<?php include 'includes/user_footer.php'; ?>