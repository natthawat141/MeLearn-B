<!-- register.php -->
<?php
session_start();
require_once '../config/database.php';
include 'includes/user_header.php';

$error = '';

// สร้าง CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // รับและ sanitize ข้อมูล
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $surname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    // ตรวจสอบความยาวของรหัสผ่าน
    if (strlen($password) < 8) {
        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ตรวจสอบว่าชื่อและนามสกุลเป็นภาษาอังกฤษเท่านั้น
        if (!preg_match("/^[a-zA-Z ]*$/", $name) || !preg_match("/^[a-zA-Z ]*$/", $surname)) {
            $error = "ชื่อและนามสกุลต้องเป็นภาษาอังกฤษเท่านั้น";
        } elseif (!$email) {
            $error = "รูปแบบอีเมลไม่ถูกต้อง";
        } else {
            // ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลมีอยู่แล้วหรือไม่
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "ชื่อผู้ใช้หรืออีเมลมีอยู่แล้ว";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, surname, username, email, password) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $surname, $username, $email, $hashedPassword])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['username'] = $username;
                    header("Location: profile.php");
                    exit();
                } else {
                    $error = "เกิดข้อผิดพลาดในการลงทะเบียน";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="/me/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/me/user/user_style.css">
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
</head>
<body>
    <div class="container mt-5">
        <h2>ลงทะเบียน</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="name">ชื่อ:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="surname">นามสกุล:</label>
                <input type="text" class="form-control" id="surname" name="surname" required>
            </div>
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
        </form>
        <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
    </div>
</body>
</html>

<?php include 'includes/user_footer.php'; ?>