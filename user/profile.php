<!-- profile.php -->
<?php
session_start();
require_once '../config/database.php';
include 'includes/user_header.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User not found.');
}

// สร้าง CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// อัปเดตข้อมูลผู้ใช้
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // รับและ sanitize ข้อมูล
    $new_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Validate input
    if (!preg_match("/^[a-zA-Z ]*$/", $new_name) || !preg_match("/^[a-zA-Z ]*$/", $new_surname)) {
        $error = "ชื่อและนามสกุลต้องเป็นภาษาอังกฤษเท่านั้น";
    } elseif ($new_email === false) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, username = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$new_name, $new_surname, $new_username, $new_email, $user_id])) {
            $_SESSION['username'] = $new_username;
            $success = "อัปเดตข้อมูลสำเร็จ";
            // อัปเดตข้อมูลผู้ใช้ในตัวแปร $user
            $user['name'] = $new_name;
            $user['surname'] = $new_surname;
            $user['username'] = $new_username;
            $user['email'] = $new_email;
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
    }
}
?>

<div class="container mt-5">
    <h2>โปรไฟล์ของฉัน</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <div class="form-group">
            <label for="name">ชื่อ:(ภาษาอังกฤษเท่านั้น)</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="form-group">
            <label for="surname">นามสกุล:(ภาษาอังกฤษเท่านั้น)</label>
            <input type="text" class="form-control" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="form-group">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">อีเมล:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">อัปเดตข้อมูล</button>
    </form>
</div>

<script>
function validateForm() {
    const name = document.getElementById('name').value;
    const surname = document.getElementById('surname').value;
    const namePattern = /^[a-zA-Z ]+$/;

    if (!namePattern.test(name)) {
        alert('ชื่อสามารถมีเฉพาะตัวอักษรภาษาอังกฤษเท่านั้น');
        return false;
    }

    if (!namePattern.test(surname)) {
        alert('นามสกุลสามารถมีเฉพาะตัวอักษรภาษาอังกฤษเท่านั้น');
        return false;
    }

    return true;
}
</script>

<?php include 'includes/user_footer.php'; ?>