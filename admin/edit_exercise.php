<!-- edit_exercise.php -->
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

// ตรวจสอบและรับค่า id
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "Invalid exercise ID.";
    exit();
}

// ดึงข้อมูล exercise
$stmt = $pdo->prepare("SELECT * FROM exercises WHERE id = ?");
$stmt->execute([$id]);
$exercise = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$exercise) {
    echo "Exercise not found.";
    exit();
}

// ตรวจสอบการ submit ฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
        echo "Invalid CSRF token.";
        exit();
    }

    // รับและ sanitize ข้อมูล
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');
    $option1 = htmlspecialchars($_POST['option1'], ENT_QUOTES, 'UTF-8');
    $option2 = htmlspecialchars($_POST['option2'], ENT_QUOTES, 'UTF-8');
    $option3 = htmlspecialchars($_POST['option3'], ENT_QUOTES, 'UTF-8');
    $option4 = htmlspecialchars($_POST['option4'], ENT_QUOTES, 'UTF-8');
    $correct_answer = filter_input(INPUT_POST, 'correct_answer', FILTER_VALIDATE_INT);

    // ตรวจสอบข้อมูลที่รับมา
    if ($course_id && $question && $option1 && $option2 && $option3 && $option4 && $correct_answer) {
        $stmt = $pdo->prepare("UPDATE exercises SET course_id = ?, question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_answer = ? WHERE id = ?");
        if ($stmt->execute([$course_id, $question, $option1, $option2, $option3, $option4, $correct_answer, $id])) {
            $success_message = "Exercise updated successfully.";
        } else {
            $error_message = "Error updating exercise.";
        }
    } else {
        $error_message = "All fields are required and must be valid.";
    }
}
?>

<div class="container">
    <h2>Edit Exercise</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_exercise.php?id=<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="course_id">Course</label>
            <select name="course_id" class="form-control" required>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $row['id'] == $exercise['course_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="question">Question</label>
            <textarea name="question" class="form-control" rows="4" required><?php echo htmlspecialchars($exercise['question']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="option1">Option 1</label>
            <input type="text" name="option1" class="form-control" value="<?php echo htmlspecialchars($exercise['option1']); ?>" required>
        </div>
        <div class="form-group">
            <label for="option2">Option 2</label>
            <input type="text" name="option2" class="form-control" value="<?php echo htmlspecialchars($exercise['option2']); ?>" required>
        </div>
        <div class="form-group">
            <label for="option3">Option 3</label>
            <input type="text" name="option3" class="form-control" value="<?php echo htmlspecialchars($exercise['option3']); ?>" required>
        </div>
        <div class="form-group">
            <label for="option4">Option 4</label>
            <input type="text" name="option4" class="form-control" value="<?php echo htmlspecialchars($exercise['option4']); ?>" required>
        </div>
        <div class="form-group">
            <label for="correct_answer">Correct Answer (1-4)</label>
            <input type="number" name="correct_answer" class="form-control" value="<?php echo htmlspecialchars($exercise['correct_answer']); ?>" min="1" max="4" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Exercise</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
