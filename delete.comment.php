<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] != 'admin') {
    header('Location: admin_login.php');
    exit();
}

$host = 'localhost';
$dbname = 'blog_database';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $comment_id = $_GET['id'];
    $delete_stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $delete_stmt->execute([$comment_id]);
    header('Location: blog.php');
    exit();
} else {
    echo "Yorum bulunamadı.";
    exit();
}
