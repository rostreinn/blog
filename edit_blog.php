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
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_comment = $_POST['comment'];

        if (!empty($new_comment)) {
            $update_stmt = $pdo->prepare("UPDATE comments SET comment = ? WHERE id = ?");
            $update_stmt->execute([$new_comment, $comment_id]);
            header('Location: blog.php');
            exit();
        }
    }
} else {
    echo "Yorum bulunamadı.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yorum Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Yorum Düzenle</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="comment" class="form-label">Yorumunuz</label>
                <textarea class="form-control" name="comment" id="comment" rows="3" required><?= htmlspecialchars($comment['comment']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Yorum Güncelle</button>
        </form>
    </div>
</body>
</html>
