
<!-- edit_chapter.php -->
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
    echo "Invalid chapter ID.";
    exit();
}

// ดึงข้อมูล chapter
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE id = ?");
$stmt->execute([$id]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$chapter) {
    echo "Chapter not found.";
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
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $order_number = filter_input(INPUT_POST, 'order_number', FILTER_VALIDATE_INT);

    // ตรวจสอบข้อมูลที่รับมา
    if ($course_id && $title && $description && $order_number) {
        $stmt = $pdo->prepare("UPDATE chapters SET course_id = ?, title = ?, description = ?, order_number = ? WHERE id = ?");
        if ($stmt->execute([$course_id, $title, $description, $order_number, $id])) {
            $success_message = "Chapter updated successfully.";
        } else {
            $error_message = "Error updating chapter.";
        }
    } else {
        $error_message = "All fields are required and must be valid.";
    }
}
?>

<div class="container">
    <h2>Edit Chapter</h2>

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

    <form method="POST" action="edit_chapter.php?id=<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="course_id">Course</label>
            <select name="course_id" class="form-control" required>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $row['id'] == $chapter['course_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($chapter['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($chapter['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="order_number">Order Number</label>
            <input type="number" name="order_number" class="form-control" value="<?php echo htmlspecialchars($chapter['order_number']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Chapter</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
