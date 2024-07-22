<!-- delete_exercise.php -->
<?php
require_once '../config/database.php';

// เริ่มต้น session หากยังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// สร้าง CSRF token ถ้ายังไม่มี
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// ตรวจสอบ CSRF token ในการร้องขอ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: manage_exercises.php");
        exit();
    }

    // ตรวจสอบและกรองค่า id
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $_SESSION['error'] = "Invalid exercise ID.";
        header("Location: manage_exercises.php");
        exit();
    }

    try {
        // ลบข้อมูล exercise
        $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Exercise deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting exercise";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete exercise: " . $e->getMessage();
    }

    header("Location: manage_exercises.php");
    exit();
}
?>

<!-- ฟอร์มสำหรับลบแบบฝึกหัด -->
<form method="POST" action="delete_exercise.php">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($exercise['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบแบบฝึกหัดนี้?')">ลบ</button>
</form>
