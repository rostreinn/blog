<?php
// Veritabanı Bağlantısı
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

// yazi id
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Geçersiz yazı ID'si.");
}

// yaziyi aldim
$stmt = $pdo->prepare("SELECT * FROM blog_database WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Yazı bulunamadı.");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <a href="#" class="logo">rostreinn's Base</a>
    </div>

    <!-- blog detay -->
    <section class="container mt-5">
        <h1 class="work"><?= htmlspecialchars($post['title']) ?></h1>
        <p class="text-muted">Yazar: <?= htmlspecialchars($post['author']) ?></p>
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        <a href="index.php" class="btn btn-primary mt-4">Geri Dön</a>
    </section>

    <!-- Footer -->
    <footer class="footer mt-5">
        <p class="copy">&copy; Example. Tüm hakları saklıdır.</p>
    </footer>
</body>
</html>
