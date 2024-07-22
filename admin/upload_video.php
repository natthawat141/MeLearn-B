
<!-- upload_video.php  -->
 
<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if ($course_id === null || $course_id === false) {
    echo "Invalid course ID.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['course_id'], $_POST['chapter_id'], $_POST['title'], $_POST['description'], $_POST['order_number'])) {
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
        $chapter_id = filter_input(INPUT_POST, 'chapter_id', FILTER_VALIDATE_INT);
        $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
        $order_number = filter_input(INPUT_POST, 'order_number', FILTER_VALIDATE_INT);

        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
            $target_dir = "../uploads/videos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES["video_file"]["name"], PATHINFO_EXTENSION);
            $allowed_extensions = ['mp4', 'avi', 'mov'];
            if (!in_array($file_extension, $allowed_extensions)) {
                echo "Invalid file type. Only MP4, AVI, and MOV files are allowed.";
                exit();
            }

            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
                $video_url = '/uploads/videos/' . $new_filename;
                $stmt = $pdo->prepare("INSERT INTO videos (course_id, chapter_id, title, description, video_url, order_number) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$course_id, $chapter_id, $title, $description, $video_url, $order_number])) {
                    echo "Video uploaded successfully";
                } else {
                    echo "Error saving video data";
                }
            } else {
                echo "Error uploading file";
            }
        } else {
            echo "Please select a video file";
        }
    } else {
        echo "Please fill all fields";
    }
}

// Fetch chapters for the given course
$stmt = $pdo->prepare("SELECT id, title FROM chapters WHERE course_id = ?");
$stmt->execute([$course_id]);
$chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5">
    <h2>Upload New Video</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="course_id">Select Course</label>
            <select name="course_id" id="course_id" class="form-control" required disabled>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch()) {
                    $selected = ($row['id'] == $course_id) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "' $selected>" . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="chapter_id">Select Chapter</label>
            <select name="chapter_id" id="chapter_id" class="form-control" required>
                <option value="">Select Chapter</option>
                <?php
                foreach ($chapters as $chapter) {
                    echo "<option value='" . htmlspecialchars($chapter['id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Video Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="video_file">Video File</label>
            <input type="file" name="video_file" class="form-control-file" required>
        </div>
        <div class="form-group">
            <label for="order_number">Order Number</label>
            <input type="number" name="order_number" class="form-control" required>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Upload Video</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
