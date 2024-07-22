<!-- admin_register.php -->
<?php

require_once '../config/database.php';

// Initialize an empty array to hold any errors
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password']; // password_hash will handle sanitization

    // Check for missing or invalid input
    if (!$username || !$email || !$password) {
        $errors[] = "All fields are required and must be valid.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$username, $email, $hashed_password]);
            $success = "ลงทะเบียน admin สำเร็จ";
        } catch (PDOException $e) {
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>ลงทะเบียน Admin</h2>
        <?php 
        if (!empty($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success) . "</div>";
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>";
            foreach ($errors as $error) {
                echo "<p>" . htmlspecialchars($error) . "</p>";
            }
            echo "</div>";
        }
        ?>
        <form method="POST" class="mt-4">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="container mt-3">
                <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
            </div>
        </form>
    </div>

    <script src="../bootstrap/js/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
