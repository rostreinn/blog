<?php
session_start();

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

// Yazıları veritabanından çekme
$stmt = $pdo->query("SELECT * FROM blog_database ORDER BY created_at DESC");
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Yorum eklemek için işlem
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $blog_id = $_POST['blog_id'];
        $user_id = $_SESSION['user_id']; // Giriş yapan kullanıcı ID'si
        $comment = $_POST['comment'];

        if (!empty($comment)) {
            // Veritabanına yorum ekle
            $stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$blog_id, $user_id, $comment]);
        }
    } else {
        echo "<script>alert('Yorum yapabilmek için giriş yapmalısınız!');</script>";
    }
}

// Blog yazısına ait yorumları çekme
$comments_stmt = $pdo->prepare("SELECT comments.*, uyeler.kullaniciadi FROM comments INNER JOIN uyeler ON comments.user_id = uyeler.id WHERE blog_id = ? ORDER BY comments.created_at DESC");

// Yazı silme işlemi
if (isset($_GET['delete_blog_id']) && isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
    $blog_id = $_GET['delete_blog_id'];

    // Öncelikle blog yazısına ait yorumları sil
    $stmt = $pdo->prepare("DELETE FROM comments WHERE blog_id = ?");
    $stmt->execute([$blog_id]);

    // Sonra yazıyı sil
    $stmt = $pdo->prepare("DELETE FROM blog_database WHERE id = ?");
    $stmt->execute([$blog_id]);

    header("Location: blog.php"); // Yeniden sayfayı yenileyerek silme işlemi sonrası kullanıcıyı yönlendir
    exit;
}

// Blog yazısını düzenleme işlemi
if (isset($_GET['edit_blog_id']) && isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
    $blog_id = $_GET['edit_blog_id'];
    $stmt = $pdo->prepare("SELECT * FROM blog_database WHERE id = ?");
    $stmt->execute([$blog_id]);
    $blog_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

    // Eğer post verisi varsa, yazıyı güncelle
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_blog'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];

        if (!empty($title) && !empty($content)) {
            $update_stmt = $pdo->prepare("UPDATE blog_database SET title = ?, content = ? WHERE id = ?");
            $update_stmt->execute([$title, $content, $blog_id]);

            header("Location: blog.php"); // Güncelleme işlemi sonrası sayfayı yeniden yükle
            exit;
        }
    }
}

// Yorum Silme İşlemi
if (isset($_GET['delete_comment_id']) && isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
    $comment_id = $_GET['delete_comment_id'];

    // Yorum Silme
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);

    header("Location: blog.php"); // Sayfayı yenile
    exit;
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Sayfası</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Genel stil */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .navbar {
            background-color: #333;
        }

        .navbar-brand, .nav-link {
            color: #000;
        }

        .navbar-nav .nav-link:hover {
            color: #ff9800 !important;
        }

        /* Blog Card */
        .card {
            margin-bottom: 30px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #ff9800;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .card-body {
            background-color: #fff;
            padding: 20px;
            font-size: 1.1rem;
        }

        .card-footer {
            background-color: #f9f9f9;
            color: #333;
        }

        /* Yorumlar */
        .comment-card {
            border-left: 4px solid #ff9800;
            padding-left: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .comment-card h5 {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff9800;
        }

        .comment-card .card-body {
            font-size: 1rem;
            color: #333;
        }

        /* Yorum Ekleme */
        .comment-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .comment-form label {
            font-size: 1.1rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Blog Sitesi</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Giriş Yap</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Blog Yazıları -->
        <div class="col-md-8">

            <?php foreach ($blogs as $blog): ?>
                <div class="card">
                    <div class="card-header">
                        <?= htmlspecialchars($blog['title']) ?>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                            <a href="?delete_blog_id=<?= $blog['id'] ?>" class="btn btn-danger btn-sm float-end" onclick="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?');">Sil</a>
                            <a href="?edit_blog_id=<?= $blog['id'] ?>" class="btn btn-warning btn-sm float-end me-2">Düzenle</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($blog['content'])) ?></p>
                    </div>

                    <div class="card-footer">
                        <strong>Yorumlar</strong>

                        <?php
                        // Yorumları çekme
                        $comments_stmt->execute([$blog['id']]);
                        $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card">
                                <h5><?= htmlspecialchars($comment['kullaniciadi']) ?></h5>
                                <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>

                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                                    <a href="?delete_comment_id=<?= $comment['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <!-- Yorum Ekleme -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" class="comment-form">
                                <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Yorumunuz</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Yorum Yap</button>
                            </form>
                        <?php else: ?>
                            <p><a href="login.php">Giriş yap</a> ve yorum yap.</p>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

</body>
</html>
