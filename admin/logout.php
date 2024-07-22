<?php
session_start();
session_unset(); // ล้างข้อมูลทั้งหมดใน session
session_destroy(); // ทำลาย session
header("Location: login.php"); // เปลี่ยนเส้นทางไปที่หน้า login
exit();
?>
