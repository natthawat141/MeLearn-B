<!-- add_course.php -->
<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

// Initialize an empty array to hold any errors

$errors = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $course_name = filter_input(INPUT_POST, 'course_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $thumbnail = $_FILES['thumbnail'];

    // Validate that course_name is in English only
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $course_name)) {
        $errors[] = "Course Name must be in English only.";
    } else {
        // Handle file upload for thumbnail
        $thumbnail_path = '';
        if ($thumbnail['name']) {
            $target_dir = "../uploads/thumbnails/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $thumbnail_path = $target_dir . basename($thumbnail["name"]);
            move_uploaded_file($thumbnail["tmp_name"], $thumbnail_path);
        }

        // Default value for video_url (you can change this as needed)
        $video_url = '';

        $stmt = $pdo->prepare("INSERT INTO courses (title, course_name, description, thumbnail, video_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $course_name, $description, $thumbnail_path, $video_url])) {
            $success_message = "Course added successfully";
        } else {
            $errors[] = "Error adding course.";
        }
    }
}
?>

<div class="container mt-5">
    <h2>Add New Course</h2>

    <!-- Display errors if any -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Display success message if course was added successfully -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Course Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="course_name">Course Name (ภาษาอังกฤษเท่านั้น)</label>
            <input type="text" name="course_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Course Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <br>
        <div class="form-group">
            <label for="thumbnail">Thumbnail (ภาพหน้าปก course)</label>
            <input type="file" name="thumbnail" class="form-control-file">
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Add Course</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>
