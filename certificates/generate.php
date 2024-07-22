<?php
session_start();
require_once '../config/database.php';
require('fpdf186/fpdf.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['course_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'];

// ตรวจสอบว่าผู้ใช้มีสิทธิ์ได้รับใบรับรองหรือไม่
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if ($certificate) {
    // ดึงข้อมูลผู้ใช้และคอร์ส
    $stmt = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT title, course_name FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    class PDF extends FPDF
    {
        // Page header
        function Header()
        {
            // Add logo
            $this->Image('img/melearn.jpg', 10, 6, 50); // Adjust the position and size as needed
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(80); // Move to the right
            $this->Ln(20);
        }

        // Page footer
        function Footer()
        {
            // No footer required for this design
        }
    }

    // Create instance of PDF class
    $pdf = new PDF('L', 'mm', 'A4'); // 'L' for Landscape, 'mm' for millimeters, 'A4' for size
    $pdf->AddPage();

    // Add border
    $pdf->SetDrawColor(0, 136, 255);
    $pdf->Rect(5, 5, $pdf->GetPageWidth()-10, $pdf->GetPageHeight()-10);

    // Set the font for the title
    $pdf->SetFont('Arial', 'B', 30);
    $pdf->SetTextColor(0, 136, 255);
    $pdf->Cell(0, 20, 'CERTIFICATE', 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(0, 136, 255);
    $pdf->Cell(0, 10, 'OF COMPLETION', 0, 1, 'C');
    $pdf->Ln(20);

    // Set the font for the body text
    $pdf->SetFont('Arial', '', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(0, 136, 255);
    $pdf->Cell(0, 10, $user['name'] . ' ' . $user['surname'], 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'has successfully completed the online course', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(0, 136, 255);
    $pdf->Cell(0, 10, $course['course_name'], 0, 1, 'C'); // Use course_name instead of title
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'in online course by MeLearn', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, date('F d, Y', strtotime($certificate['issue_date'])), 0, 1, 'C');
    $pdf->Ln(20);

    // Output the PDF
    $pdf->Output();
} else {
    echo "ไม่พบใบรับรอง";
}
?>