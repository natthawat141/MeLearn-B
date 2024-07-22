<!-- submit_exercises.php -->
<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['course_id']) || !isset($_POST['answer'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$answers = $_POST['answer'];

// ตรวจสอบคำตอบ
$correct_answers = 0;
$total_questions = count($answers);

foreach ($answers as $exercise_id => $user_answer) {
    $stmt = $pdo->prepare("SELECT correct_answer FROM exercises WHERE id = ?");
    $stmt->execute([$exercise_id]);
    $correct_answer = $stmt->fetchColumn();

    if ($user_answer == $correct_answer) {
        $correct_answers++;
    }
}

$score = ($correct_answers / $total_questions) * 100;

// บันทึกผลคะแนน
$stmt = $pdo->prepare("INSERT INTO user_exercise_results (user_id, course_id, score) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE score = ?");
$stmt->execute([$user_id, $course_id, $score, $score]);

// ตรวจสอบว่าผ่านเกณฑ์หรือไม่ (สมมติว่าต้องได้คะแนน 70% ขึ้นไป)
$completed = ($score >= 70) ? 1 : 0;

$stmt = $pdo->prepare("UPDATE user_courses SET completed = ? WHERE user_id = ? AND course_id = ?");
$stmt->execute([$completed, $user_id, $course_id]);

if ($completed) {
    // เพิ่มข้อมูลใบรับรอง
    $stmt = $pdo->prepare("INSERT INTO certificates (user_id, course_id, issue_date) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $course_id]);
}

$message = ($completed) ? "ยินดีด้วย! คุณผ่านแบบฝึกหัดและได้รับใบรับรองแล้ว" : "คุณยังไม่ผ่านเกณฑ์ กรุณาทบทวนเนื้อหาและลองทำแบบฝึกหัดอีกครั้ง";

// แสดงผลลัพธ์
include 'includes/course_header.php';
?>

<h2>ผลการทำแบบฝึกหัด</h2>
<p>คะแนนของคุณ: <?php echo $score; ?>%</p>
<p><?php echo $message; ?></p>
<a href="view.php?id=<?php echo $course_id; ?>" class="btn">กลับไปยังหน้าคอร์ส</a>
<?php if ($completed): ?>
    <a href="../certificates/generate.php?course_id=<?php echo $course_id; ?>" class="btn">รับใบรับรอง</a>
<?php endif; ?>

<?php include 'includes/course_footer.php'; ?>