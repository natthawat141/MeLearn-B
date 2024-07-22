<!-- add_chapter.php -->

<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if ($course_id === null || $course_id === false) {
    echo "Invalid course ID.";
    exit();
}

// Initialize an empty array to hold any errors
$errors = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $order_number = filter_input(INPUT_POST, 'order_number', FILTER_VALIDATE_INT);

    // Check for missing or invalid input
    if (!$course_id || !$title || !$description || !$order_number) {
        $errors[] = "All fields are required and must be valid.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $pdo->prepare("INSERT INTO chapters (course_id, title, description, order_number) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $description, $order_number])) {
            $success_message = "Chapter added successfully";
        } else {
            $errors[] = "Error adding chapter.";
        }
    }
}

// Fetch courses for the given course
$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Add New Chapter</h2>

    <!-- Display errors if any -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Display success message if chapter was added successfully -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_chapter.php?course_id=<?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="course_id">Course</label>
            <select name="course_id" id="course_id" class="form-control" required disabled>
                <option value="<?php echo htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></option>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="order_number">Order Number</label>
            <input type="number" name="order_number" class="form-control" required>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Add Chapter</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>

