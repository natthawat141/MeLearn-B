<?php
session_start();
require_once '../config/database.php';

// ตรวจสอบว่าผู้ใช้ได้ล็อกอินแล้วหรือยัง
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}

$error = '';
$attempts = 0; // จำนวนครั้งที่พยายามล็อกอิน

// สร้าง CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบ CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // ตรวจสอบการพยายามล็อกอินเกินจำนวนที่กำหนด
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempt_time > (NOW() - INTERVAL 10 MINUTE)");
    $stmt->execute([$username, $ip_address]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 5) {
        $error = "คุณพยายามล็อกอินเกินจำนวนที่กำหนด โปรดลองใหม่อีกครั้งใน 10 วินาที";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // ป้องกัน Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: my_courses.php");
            exit();
        } else {
            // บันทึกการพยายามล็อกอิน
            $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)");
            $stmt->execute([$username, $ip_address]);
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/me/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/me/user/user_style.css">
    <style>
        .form-group input[type="password"] {
            font-family: 'password';
        }
    </style>
    <script>
        function delay(seconds) {
            return new Promise(resolve => setTimeout(resolve, seconds * 1000));
        }

        async function handleFormSubmit(event) {
            event.preventDefault();
            const attempts = <?php echo $attempts; ?>;
            if (attempts >= 5) {
                alert('คุณพยายามล็อกอินเกินจำนวนที่กำหนด โปรดลองใหม่อีกครั้งใน 10 วินาที');
                await delay(10);
            }
            document.getElementById('loginForm').submit();
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>เข้าสู่ระบบ</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php" id="loginForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">รหัสผ่าน:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
        </form>
        <p>ยังไม่มีบัญชี? <a href="register.php">ลงทะเบียนที่นี่</a></p>
        
        <!-- แสดงจำนวนครั้งที่พยายามล็อกอิน ถ้ามีมากกว่า 1 ครั้ง -->
        <?php if ($attempts > 1): ?>
            <p>คุณพยายามล็อกอินแล้ว: <?php echo htmlspecialchars($attempts, ENT_QUOTES, 'UTF-8'); ?> ครั้ง</p>
        <?php endif; ?>
    </div>
</body>
</html>