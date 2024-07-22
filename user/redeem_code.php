<!-- redeem_code.php -->
<?php
if (!defined('REDEEM_CODE_INCLUDED')) {
    define('REDEEM_CODE_INCLUDED', true);

    function redeem_code($pdo, $user_id, $code) {
        $redeem_success = '';
        $redeem_error = '';

        // ตรวจสอบ code
        $stmt = $pdo->prepare("SELECT * FROM redeem_codes WHERE code = ? AND is_used = 0");
        $stmt->execute([$code]);
        $redeem_code = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($redeem_code) {
            $course_id = $redeem_code['course_id'];
            
            // ตรวจสอบว่าผู้ใช้มีคอร์สนี้อยู่แล้วหรือไม่
            $stmt = $pdo->prepare("SELECT * FROM user_courses WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course_id]);
            $existing_course = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_course) {
                $redeem_error = "คุณมีคอร์สนี้อยู่แล้ว";
            } else {
                // เพิ่มคอร์สให้กับผู้ใช้
                $stmt = $pdo->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (?, ?)");
                if ($stmt->execute([$user_id, $course_id])) {
                    // อัปเดตสถานะ code เป็นใช้แล้ว
                    $stmt = $pdo->prepare("UPDATE redeem_codes SET is_used = 1, used_by = ? WHERE id = ?");
                    $stmt->execute([$user_id, $redeem_code['id']]);
                    $redeem_success = "ใช้ Redeem Code สำเร็จ! คุณได้รับสิทธิ์เข้าถึงคอร์สแล้ว";
                } else {
                    $redeem_error = "เกิดข้อผิดพลาดในการเพิ่มคอร์ส";
                }
            }
        } else {
            $redeem_error = "Redeem Code ไม่ถูกต้องหรือถูกใช้ไปแล้ว";
        }

        return [$redeem_success, $redeem_error];
    }
}
?>