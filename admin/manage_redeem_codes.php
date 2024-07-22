<!-- 
 manage_redeem_codes.php
-->

<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

function generateUniqueCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// สร้าง redeem code
if (isset($_POST['create_code'])) {
    $course_id = $_POST['course_id'];
    $code = generateUniqueCode(); // ฟังก์ชันสร้าง code แบบสุ่ม

    $stmt = $pdo->prepare("INSERT INTO redeem_codes (code, course_id) VALUES (?, ?)");
    if ($stmt->execute([$code, $course_id])) {
        echo "<div class='alert alert-success mt-3'>สร้าง Redeem Code สำเร็จ: $code</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>เกิดข้อผิดพลาดในการสร้าง Redeem Code</div>";
    }
}

// แสดงรายการ redeem codes
$stmt = $pdo->query("SELECT r.*, c.title as course_title, u.username as used_by_username FROM redeem_codes r 
                     JOIN courses c ON r.course_id = c.id 
                     LEFT JOIN users u ON r.used_by = u.id 
                     ORDER BY r.id DESC");
$codes = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>จัดการ Redeem Codes</h2>

    <h3>สร้าง Redeem Code ใหม่</h3>
    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="course_id">เลือกคอร์ส:</label>
            <select class="form-control" id="course_id" name="course_id" required>
                <?php
                $stmt = $pdo->query("SELECT id, title FROM courses");
                while ($row = $stmt->fetch()) {
                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <br>
        <button type="submit" name="create_code" class="btn btn-primary">สร้าง Redeem Code</button>
    </form>

    <h3>รายการ Redeem Codes</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th>
                <th>คอร์ส</th>
                <th>สถานะ</th>
                <th>ใช้โดย</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($codes as $code): ?>
            <tr>
                <td><?php echo htmlspecialchars($code['code']); ?></td>
                <td><?php echo htmlspecialchars($code['course_title']); ?></td>
                <td><?php echo $code['is_used'] ? 'ใช้แล้ว' : 'ยังไม่ใช้'; ?></td>
                <td><?php echo htmlspecialchars($code['used_by_username'] ?? 'ยังไม่ได้ใช้งาน'); ?></td>    
            </tr> 
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>
