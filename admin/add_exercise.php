<!-- add_exercise.php -->
<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

// Initialize an empty array to hold any errors
$errors = [];
$success_messages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $questions = filter_var_array($_POST['questions'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $options = $_POST['options'];
    $correct_answers = filter_var_array($_POST['correct_answers'], FILTER_VALIDATE_INT);

    if (!$course_id) {
        $errors[] = "Invalid course selected.";
    }

    foreach ($questions as $index => $question) {
        if (empty($question) || !is_array($options[$index]) || count($options[$index]) != 4 || !in_array($correct_answers[$index], [1, 2, 3, 4])) {
            $errors[] = "Invalid data for question " . ($index + 1) . ".";
        }
    }

    if (empty($errors)) {
        foreach ($questions as $index => $question) {
            $stmt = $pdo->prepare("INSERT INTO exercises (course_id, question, option1, option2, option3, option4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$course_id, $question, $options[$index][0], $options[$index][1], $options[$index][2], $options[$index][3], $correct_answers[$index]])) {
                $success_messages[] = "Exercise " . ($index + 1) . " added successfully.";
            } else {
                $errors[] = "Error adding exercise " . ($index + 1) . ".";
            }
        }
    }
}
?>

<div class="container mt-5">
    <h2>Add New Exercises</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_messages)): ?>
        <div class="alert alert-success">
            <?php foreach ($success_messages as $message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="course_id">Select Course</label>
            <select name="course_id" class="form-control" required>
                <option value="">Select a course</option>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div id="exercises-container">
            <div class="exercise">
                <div class="form-group">
                    <label for="questions[]">Question</label>
                    <textarea name="questions[]" class="form-control" rows="4" required></textarea>
                </div>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <div class="form-group">
                    <label for="options[0][]">Option <?php echo $i; ?></label>
                    <input type="text" name="options[0][]" class="form-control" required>
                </div>
                <?php endfor; ?>
                <div class="form-group">
                    <label for="correct_answers[]">Correct Answer</label>
                    <input type="number" name="correct_answers[]" class="form-control" min="1" max="4" required>
                </div>
                <hr>
            </div>
        </div>
        <button type="button" id="add-exercise" class="btn btn-secondary">Add Another Exercise</button>
        <button type="submit" class="btn btn-primary">Add Exercises</button>
    </form>
</div>

<script>
document.getElementById('add-exercise').addEventListener('click', function() {
    let container = document.getElementById('exercises-container');
    let index = container.children.length;
    let newExercise = document.createElement('div');
    newExercise.classList.add('exercise');
    newExercise.innerHTML = `
        <div class="form-group">
            <label for="questions[]">Question</label>
            <textarea name="questions[]" class="form-control" rows="4" required></textarea>
        </div>
        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="form-group">
            <label for="options[${index}][]">Option ${i}</label>
            <input type="text" name="options[${index}][]" class="form-control" required>
        </div>
        <?php endfor; ?>
        <div class="form-group">
            <label for="correct_answers[]">Correct Answer</label>
            <input type="number" name="correct_answers[]" class="form-control" min="1" max="4" required>
        </div>
        <hr>
    `;
    container.appendChild(newExercise);
});
</script>

<?php include 'includes/admin_footer.php'; ?>
