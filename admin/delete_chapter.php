<!-- delete_chapter.php -->
<?php
require_once '../config/database.php';
session_start();

if (isset($_GET['id']) && isset($_GET['token'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $token = $_GET['token'];

    if ($id && hash_equals($_SESSION['token'], $token)) {
        $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Chapter deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting chapter";
        }
    } else {
        $_SESSION['error'] = "Invalid request";
    }
} else {
    $_SESSION['error'] = "Invalid request";
}

header("Location: manage_chapters.php");
exit();
?>
