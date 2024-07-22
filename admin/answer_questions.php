
<!-- answer_questions.php -->
 
<?php 
include 'includes/admin_header.php';
require_once '../config/database.php';

// แสดงคำถามที่ยังไม่ได้ตอบ
$stmt = $pdo->query("SELECT * FROM user_questions WHERE status = 'pending' ORDER BY created_at DESC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่วนของการตอบคำถาม
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    $answer = filter_input(INPUT_POST, 'answer', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Check for missing or invalid input
    if (!$question_id || !$answer) {
        $errors[] = "Both question ID and answer are required and must be valid.";
    } else {
        $stmt = $pdo->prepare("UPDATE user_questions SET answer = ?, status = 'answered', answered_at = CURRENT_TIMESTAMP WHERE id = ?");
        if ($stmt->execute([$answer, $question_id])) {
            $success_message = "ตอบคำถามสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการตอบคำถาม";
        }
    }
}
?>

<div class="container mt-5">
    <h2>ตอบคำถามผู้ใช้</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (count($questions) > 0): ?>
        <?php foreach ($questions as $question): ?>
            <div class="card my-4">
                <div class="card-header">
                    คำถามจาก User ID: <?php echo htmlspecialchars($question['user_id']); ?>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question['id']); ?>">
                        <div class="form-group">
                            <label for="answer_<?php echo $question['id']; ?>">คำตอบ:</label>
                            <textarea class="form-control" id="answer_<?php echo $question['id']; ?>" name="answer" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ส่งคำตอบ</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">ไม่มีคำถามที่รอการตอบในขณะนี้</div>
    <?php endif; ?>
</div>

<?php include 'includes/admin_footer.php'; ?>
