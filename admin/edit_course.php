<!-- edit_course.php -->
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
    echo "Invalid course ID.";
    exit();
}

// ดึงข้อมูล course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo "Course not found.";
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
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $course_name = htmlspecialchars($_POST['course_name'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $thumbnail = $_FILES['thumbnail'];

    // ตรวจสอบว่า course_name เป็นภาษาอังกฤษเท่านั้น
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $course_name)) {
        $error_message = "Course Name must be in English only.";
    } else {
        // Handle file upload for thumbnail
        $thumbnail_path = $course['thumbnail'];
        if ($thumbnail['name']) {
            $target_dir = "../uploads/thumbnails/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $thumbnail_path = $target_dir . basename($thumbnail["name"]);
            move_uploaded_file($thumbnail["tmp_name"], $thumbnail_path);
        }

        // อัปเดตข้อมูลคอร์ส
        $stmt = $pdo->prepare("UPDATE courses SET title = ?, course_name = ?, description = ?, thumbnail = ? WHERE id = ?");
        if ($stmt->execute([$title, $course_name, $description, $thumbnail_path, $id])) {
            $success_message = "Course updated successfully.";
        } else {
            $error_message = "Error updating course.";
        }
    }
}
?>

<div class="container mt-5">
    <h2>Edit Course</h2>

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

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="title">Course Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($course['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="course_name">Course Name (ภาษาอังกฤษเท่านั้น)</label>
            <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Course Description</label>
            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($course['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail</label>
            <input type="file" name="thumbnail" class="form-control-file">
            <?php if ($course['thumbnail']): ?>
                <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Thumbnail" class="img-thumbnail mt-2" style="max-width: 150px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Course</button>
    </form>
    <br>
    <a href="manage_chapters.php?course_id=<?php echo htmlspecialchars($course['id']); ?>" class="btn btn-info">Manage Chapters</a>
</div>

<?php include 'includes/admin_footer.php'; ?>
