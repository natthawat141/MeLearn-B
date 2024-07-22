<!-- course_detail.php -->
<?php
// เริ่มต้นเซสชัน
session_start();

// ดึงการตั้งค่าการเชื่อมต่อฐานข้อมูล
require_once '../config/database.php';

// รวมไฟล์ header
include 'includes/user_header.php';

// รวมไฟล์ redeem_code.php
require 'redeem_code.php';

// ตรวจสอบว่ามีการระบุ course_id หรือไม่ ถ้าไม่มีก็ redirect ไปหน้า index.php
$course_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$course_id) {
    header("Location: index.php");
    exit();
}

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ดึงข้อมูลคอร์สจากฐานข้อมูลโดยใช้ course_id
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

// ถ้าคอร์สไม่มีอยู่ในฐานข้อมูล ให้แสดงข้อความ "ไม่พบคอร์สที่ต้องการ"
if (!$course) {
    echo "<p>ไม่พบคอร์สที่ต้องการ</p>";
    include 'includes/user_footer.php';
    exit();
}

// ดึงข้อมูลบทที่ 1 ของคอร์ส
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE course_id = ? ORDER BY order_number ASC LIMIT 1");
$stmt->execute([$course_id]);
$first_chapter = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลวิดีโอของบทที่ 1
$first_video = null;
if ($first_chapter) {
    $chapter_id = $first_chapter['id'];
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE chapter_id = ? ORDER BY order_number ASC LIMIT 1");
    $stmt->execute([$chapter_id]);
    $first_video = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
$user_has_access = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // สมมติว่า user_id ถูกเก็บใน session เมื่อผู้ใช้เข้าสู่ระบบ

    // ตรวจสอบว่าผู้ใช้ที่ล็อกอินมีสิทธิ์เข้าถึงคอร์สนี้หรือไม่
    $stmt = $pdo->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $user_course = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user_course) {
        $user_has_access = true;
    }
}

// ตัวแปรสำหรับการแสดงผลการใช้ Redeem Code
$redeem_success = '';
$redeem_error = '';

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
    <div class="row">
        <div class="col-md-8">
            <div class="course-detail-video mb-4">
                <h2 class="course-title"><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                <div id="video-content">
                    <?php if ($first_video) : ?>
                        <h4><?php echo htmlspecialchars($first_video['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <video controls class="w-100" onended="updateProgress(<?php echo htmlspecialchars($first_video['id'], ENT_QUOTES, 'UTF-8'); ?>)" style="border-radius: 10px;">
                            <source src="/me/<?php echo htmlspecialchars($first_video['video_url'], ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <p><?php echo nl2br(htmlspecialchars($first_video['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php else : ?>
                        <p>ไม่มีวิดีโอสำหรับบทแรก</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="course-progress mb-4">
                <h4>ความคืบหน้าของคุณ</h4>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" disabled>ดาวน์โหลดเอกสารประกอบการเรียน</button>
                    <button class="btn btn-success disabled" aria-disabled="true" style="margin: 10px;">รับใบรับรอง</button>
                </div>
            </div>

            <div class="chapter-list">
                <h5>แนะนำ course เรียน</h5>
                <p><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="course-actions mt-4">
                <?php if ($user_has_access) : ?>
                    <a href="/me/courses/view.php?id=<?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success">เริ่มเรียน</a>
                <?php else : ?>
                    <h4>ใช้ Redeem Code เพื่อเข้าถึงคอร์สนี้</h4>
                    <!-- แสดงผลการใช้ Redeem Code -->
                    <?php if ($redeem_success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($redeem_success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($redeem_error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($redeem_error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-group">
                            <label for="redeem_code">Redeem Code:</label>
                            <input type="text" class="form-control" id="redeem_code" name="redeem_code" required>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">ใช้ Redeem Code</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันสำหรับอัปเดตความคืบหน้าเมื่อดูวิดีโอจบ
    function updateProgress(videoId) {
        fetch('update_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    video_id: videoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // อัปเดตแถบความคืบหน้า
                    const progressBar = document.querySelector('.progress-bar');
                    progressBar.style.width = `${data.progress}%`;
                    progressBar.setAttribute('aria-valuenow', data.progress);
                    progressBar.textContent = `${data.progress}%`;

                    // ถ้าความคืบหน้าเต็ม 100% ให้เปิดการใช้งานปุ่มรับใบรับรอง
                    if (data.progress >= 100) {
                        const certificateButton = document.querySelector('.btn-success');
                        certificateButton.classList.remove('disabled');
                        certificateButton.setAttribute('aria-disabled', 'false');
                        certificateButton.href = "/me/certificates/generate.php";
                    }
                } else {
                    console.error('Error updating progress:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>

<?php include 'includes/user_footer.php'; ?>