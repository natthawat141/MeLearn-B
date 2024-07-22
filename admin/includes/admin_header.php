<!-- admin_header.php -->
<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /me/admin/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/me/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/me/admin/admin_style.css">
</head>
<body>
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="/me/admin/index.php" style="margin:5px">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="manage_courses.php">Manage Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_redeem_codes.php">Manage Redeem Codes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="answer_questions.php">Answer Questions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <main class="container mt-4">
        