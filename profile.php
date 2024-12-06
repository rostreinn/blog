<?php
session_start();
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

// Kullanıcı oturumu kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı bilgilerini çek
$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT * FROM uyeler WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Kullanıcı bulunamadı!";
    exit();
}

// Kullanıcı yorumlarını çek
$comments_query = $pdo->prepare("SELECT c.comment, b.title, c.created_at 
                                FROM comments c 
                                JOIN blog_database b ON c.blog_id = b.id 
                                WHERE c.user_id = ?");
$comments_query->execute([$user_id]);
$comments = $comments_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 text-center">
                <!-- Profil Fotoğrafı -->
                <form action="upload_photo.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <?php if (file_exists("uploads/{$user['id']}.jpg")): ?>
                            <img src="uploads/<?= $user['id'] ?>.jpg" class="profile-photo" alt="Profil Fotoğrafı">
                        <?php else: ?>
                            <img src="default-avatar.jpg" class="profile-photo" alt="Varsayılan Fotoğraf">
                        <?php endif; ?>
                    </div>
                    <input type="file" name="profile_photo" class="form-control mb-3" accept="image/*">
                    <button type="submit" class="btn btn-primary">Fotoğraf Yükle</button>
                </form>
            </div>
            <div class="col-md-8">
                <h1>Hoşgeldiniz, <?= htmlspecialchars($user['kullaniciadi']); ?>!</h1>
                <p><strong>Kayıt Tarihi:</strong> <?= date('d M Y', strtotime($user['created_at'])); ?></p>
                <hr>
                <h3>Yorumlarınız</h3>
                <?php if (count($comments) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($comments as $comment): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($comment['title']); ?>:</strong> <?= htmlspecialchars($comment['comment']); ?>
                                <br>
                                <small class="text-muted">Tarih: <?= date('d M Y, H:i', strtotime($comment['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Henüz yorum yapmamışsınız.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
