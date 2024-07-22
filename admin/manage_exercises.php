<!-- manage_exercises.php -->

<?php 
include 'includes/admin_header.php';
require_once '../config/database.php';

// เริ่มต้น session หากยังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รับข้อความแจ้งเตือนจาก session
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// ลบข้อความแจ้งเตือนหลังจากแสดงผลแล้ว
unset($_SESSION['success']);
unset($_SESSION['error']);

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['token'];

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if ($course_id) {
    // แสดงรายการแบบฝึกหัดที่มีอยู่
    $stmt = $pdo->prepare("SELECT exercises.*, courses.title as course_title FROM exercises 
                        LEFT JOIN courses ON exercises.course_id = courses.id
                        WHERE exercises.course_id = ?");
    $stmt->execute([$course_id]);
    $exercises = $stmt->fetchAll();
    
    // ส่วนของการเพิ่มแบบฝึกหัดใหม่
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // ตรวจสอบ CSRF token
        if (!isset($_POST['token']) || !hash_equals($token, $_POST['token'])) {
            $_SESSION['error'] = "Invalid CSRF token.";
            header("Location: manage_exercises.php?course_id=" . urlencode($course_id));
            exit();
        }

        $questions = $_POST['questions'];
        $options = $_POST['options'];
        $correct_answers = $_POST['correct_answers'];

        foreach ($questions as $index => $question) {
            $stmt = $pdo->prepare("INSERT INTO exercises (course_id, question, option1, option2, option3, option4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$course_id, $question, $options[$index][0], $options[$index][1], $options[$index][2], $options[$index][3], $correct_answers[$index]])) {
                $_SESSION['success'] = "เพิ่มแบบฝึกหัดสำเร็จ";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มแบบฝึกหัด";
            }
        }
        header("Location: manage_exercises.php?course_id=" . urlencode($course_id));
        exit();
    }
} else {
    $_SESSION['error'] = "ไม่มี course_id ระบุใน URL";
    $exercises = [];
}
?>

<div class="container mt-5">
    <h2>จัดการแบบฝึกหัด</h2>

    <!-- แสดงข้อความแจ้งเตือน -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($course_id): ?>
        <!-- แสดงรายการแบบฝึกหัด -->
        <h3>รายการแบบฝึกหัดสำหรับคอร์ส <?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?></h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>คำถาม</th>
                    <th>คอร์ส</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?php echo htmlspecialchars($exercise['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($exercise['question'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($exercise['course_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="edit_exercise.php?id=<?php echo htmlspecialchars($exercise['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                        <form method="POST" action="delete_exercise.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($exercise['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบแบบฝึกหัดนี้?')">ลบ</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ฟอร์มเพิ่มแบบฝึกหัดใหม่ -->
        <h3>เพิ่มแบบฝึกหัดใหม่สำหรับคอร์ส <?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?></h3>
        <form method="POST" class="mt-3">
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
            <div id="exercises-container">
                <div class="exercise">
                    <div class="form-group">
                        <label for="questions[]">คำถาม:</label>
                        <textarea class="form-control" id="questions[]" name="questions[]" rows="3" required></textarea>
                    </div>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group">
                        <label for="options[0][]">ตัวเลือกที่ <?php echo $i; ?>:</label>
                        <input type="text" class="form-control" id="options[0][]" name="options[0][]" required>
                    </div>
                    <?php endfor; ?>
                    <div class="form-group">
                        <label for="correct_answers[]">คำตอบที่ถูกต้อง (1-4):</label>
                        <input type="number" class="form-control" id="correct_answers[]" name="correct_answers[]" min="1" max="4" required>
                    </div>
                    <hr>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">เพิ่มแบบฝึกหัด</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/admin_footer.php'; ?>

