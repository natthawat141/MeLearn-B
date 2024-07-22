
<!-- edit_video.php -->
 
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
    echo "Invalid video ID.";
    exit();
}

// ดึงข้อมูลวิดีโอ
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$video) {
    echo "Video not found.";
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
    $chapter_id = filter_input(INPUT_POST, 'chapter_id', FILTER_VALIDATE_INT);
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    $order_number = filter_input(INPUT_POST, 'order_number', FILTER_VALIDATE_INT);
    $video_url = $video['video_url'];

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $target_dir = "../uploads/videos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
            // ลบไฟล์วิดีโอเก่า
            if (file_exists($video_url)) {
                unlink($video_url);
            }
            $video_url = '/uploads/videos/' . $new_filename;
        } else {
            echo "Error uploading file.";
        }
    }

    // ตรวจสอบข้อมูลที่รับมา
    if ($course_id && $chapter_id && $title && $description && $order_number) {
        $stmt = $pdo->prepare("UPDATE videos SET course_id = ?, chapter_id = ?, title = ?, description = ?, video_url = ?, order_number = ? WHERE id = ?");
        if ($stmt->execute([$course_id, $chapter_id, $title, $description, $video_url, $order_number, $id])) {
            $success_message = "Video updated successfully.";
        } else {
            $error_message = "Error updating video.";
        }
    } else {
        $error_message = "All fields are required and must be valid.";
    }
}
?>

<div class="container mt-5">
    <h2>Edit Video</h2>

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

    <form method="POST" enctype="multipart/form-data" action="edit_video.php?id=<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="course_id">Select Course</label>
            <select name="course_id" class="form-control" required>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $row['id'] == $video['course_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="chapter_id">Select Chapter</label>
            <select name="chapter_id" class="form-control" required>
                <?php
                $stmt = $pdo->prepare("SELECT id, title FROM chapters WHERE course_id = ?");
                $stmt->execute([$video['course_id']]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $row['id'] == $video['chapter_id'] ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Video Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($video['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($video['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="video_file">Video File</label>
            <input type="file" name="video_file" class="form-control-file">
            <?php if ($video['video_url']): ?>
                <video width="320" height="240" controls class="mt-2">
                    <source src="/me<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="order_number">Order Number</label>
            <input type="number" name="order_number" class="form-control" value="<?php echo htmlspecialchars($video['order_number']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Video</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
