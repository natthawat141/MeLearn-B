<!-- exercise.php -->
<?php
session_start();
require_once '../config/database.php';
include 'includes/course_header.php';


if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$course_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<p>ไม่พบคอร์สที่ต้องการ</p>";
    include 'includes/course_footer.php';
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM exercises WHERE course_id = ?");
$stmt->execute([$course_id]);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_exercises = count($exercises);
?>

<div class="course-exercise-container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-80">
            <div class="exercise-header mb-4">
                <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" class="img-fluid course-thumbnail" alt="Course Image">
                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                <div class="exercise-number">
                    <span id="exercise-number">1</span> / <?php echo $total_exercises; ?>
                </div>
            </div>
            <?php if ($total_exercises > 0) : ?>
                <form method="POST" action="submit_exercises.php" id="exercise-form">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <?php foreach ($exercises as $index => $exercise) : ?>
                        <div class="exercise mb-4" id="exercise-<?php echo $index; ?>" style="display: <?php echo $index == 0 ? 'block' : 'none'; ?>;">
                            <p><strong><?php echo ($index + 1) . '. ' . htmlspecialchars($exercise['question']); ?></strong></p>
                            <?php for ($i = 1; $i <= 4; $i++) : ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer[<?php echo $exercise['id']; ?>]" value="<?php echo $i; ?>" id="exercise-<?php echo $exercise['id']; ?>-option-<?php echo $i; ?>" required>
                                    <label class="form-check-label" for="exercise-<?php echo $exercise['id']; ?>-option-<?php echo $i; ?>">
                                        <?php echo htmlspecialchars($exercise['option' . $i]); ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center btn-group">
                        <button type="button" class="btn btn-secondary" onclick="prevExercise()" id="prev-button" style="display: none;">คำถามก่อนหน้า</button>
                        <button type="button" class="btn btn-primary" onclick="nextExercise()">คำถามต่อไป</button>
                        <button type="submit" class="btn btn-success" id="submit-button" style="display: none;">ส่งคำตอบ</button>
                    </div>
                </form>
            <?php else : ?>
                <p>ไม่มีแบบฝึกหัดสำหรับคอร์สนี้</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    let currentExercise = 0;
    const totalExercises = <?php echo $total_exercises; ?>;

    function nextExercise() {
        const exerciseDiv = document.getElementById(`exercise-${currentExercise}`);
        if (exerciseDiv) {
            exerciseDiv.style.display = 'none';
        }
        currentExercise++;
        if (currentExercise < totalExercises) {
            const nextExerciseDiv = document.getElementById(`exercise-${currentExercise}`);
            if (nextExerciseDiv) {
                nextExerciseDiv.style.display = 'block';
            }
            document.getElementById('exercise-number').innerText = currentExercise + 1;
        }
        if (currentExercise === totalExercises - 1) {
            document.querySelector('button[onclick="nextExercise()"]').style.display = 'none';
            document.getElementById('submit-button').style.display = 'block';
        }
        document.getElementById('prev-button').style.display = 'block';
    }

    function prevExercise() {
        const exerciseDiv = document.getElementById(`exercise-${currentExercise}`);
        if (exerciseDiv) {
            exerciseDiv.style.display = 'none';
        }
        currentExercise--;
        if (currentExercise >= 0) {
            const prevExerciseDiv = document.getElementById(`exercise-${currentExercise}`);
            if (prevExerciseDiv) {
                prevExerciseDiv.style.display = 'block';
            }
            document.getElementById('exercise-number').innerText = currentExercise + 1;
        }
        if (currentExercise === 0) {
            document.getElementById('prev-button').style.display = 'none';
        }
        document.querySelector('button[onclick="nextExercise()"]').style.display = 'block';
        document.getElementById('submit-button').style.display = 'none';
    }
</script>

<?php include 'includes/course_footer.php'; ?>
