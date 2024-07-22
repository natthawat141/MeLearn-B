<!-- index.php -->
<?php
include 'includes/admin_header.php';
require_once '../config/database.php';

// เริ่มต้น session หากยังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // ดึงข้อมูลสถิติต่างๆ
    $course_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $exercise_count = $pdo->query("SELECT COUNT(*) FROM exercises")->fetchColumn();
    $redeem_code_count = $pdo->query("SELECT COUNT(*) FROM redeem_codes")->fetchColumn();
    $redeem_code_used_count = $pdo->query("SELECT COUNT(*) FROM redeem_codes WHERE is_used = 1")->fetchColumn();

    // ดึงข้อมูลผู้เรียนที่เพิ่มขึ้นใน 1 สัปดาห์
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM users 
        WHERE created_at >= CURDATE() - INTERVAL 7 DAY 
        GROUP BY DATE(created_at)
    ");
    $users_per_day = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // เตรียมข้อมูลสำหรับ Chart.js
    $dates = [];
    $user_counts = [];
    foreach ($users_per_day as $day) {
        $dates[] = $day['date'];
        $user_counts[] = $day['count'];
    }
} catch (PDOException $e) {
    // จัดการข้อผิดพลาดของฐานข้อมูล
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<div class="container mt-5">
    <h2>ยินดีต้อนรับสู่ระบบจัดการ Admin</h2>
    <p>เลือกเมนูด้านบนเพื่อจัดการระบบ</p>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">จำนวนคอร์ส</h5>
                    <p class="card-text"><?php echo htmlspecialchars($course_count); ?> คอร์ส</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">จำนวนผู้ใช้งาน</h5>
                    <p class="card-text"><?php echo htmlspecialchars($user_count); ?> คน</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">จำนวนแบบฝึกหัด</h5>
                    <p class="card-text"><?php echo htmlspecialchars($exercise_count); ?> แบบฝึกหัด</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">จำนวน Redeem Codes</h5>
                    <p class="card-text"><?php echo htmlspecialchars($redeem_code_count); ?> รหัส (ใช้แล้ว <?php echo htmlspecialchars($redeem_code_used_count); ?> รหัส)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">จำนวนผู้ใช้ที่เพิ่มขึ้นใน 1 สัปดาห์</h5>
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx1 = document.getElementById('usersChart').getContext('2d');
    var usersChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'User Count',
                data: <?php echo json_encode($user_counts); ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>
