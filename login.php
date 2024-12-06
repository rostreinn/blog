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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Kullanıcı kontrolü
    $stmt = $pdo->prepare("SELECT * FROM uyeler WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Oturum başlangıcı
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['kullaniciadi'] = $user['kullaniciadi']; // Kullanıcı adını kaydet
        $_SESSION['user_role'] = $user['user_role'];
        header("Location: index.php");
        exit();
    } else {
        $error = 'E-posta veya şifre hatalı.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Giriş Yap</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php" id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">E-posta:</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div id="emailError" class="error-message" style="display: none;">Lütfen geçerli bir e-posta adresi girin.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre:</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div id="passwordError" class="error-message" style="display: none;">Şifre alanı boş olamaz.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="submitBtn">Giriş Yap</button>
        </form>
        <p class="text-center mt-3">Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Formun submit edilmeden önce doğrulama
            $('#loginForm').on('submit', function(event) {
                var valid = true;

                // E-posta doğrulaması
                var email = $('#email').val();
                var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                if (!emailPattern.test(email)) {
                    $('#emailError').show();
                    valid = false;
                } else {
                    $('#emailError').hide();
                }

                // Şifre doğrulaması
                var password = $('#password').val();
                if (password === '') {
                    $('#passwordError').show();
                    valid = false;
                } else {
                    $('#passwordError').hide();
                }

                // Eğer herhangi bir hata varsa formu göndermeyi engelle
                if (!valid) {
                    event.preventDefault();
                }
            });

            // E-posta alanında değişiklik olduğunda hata mesajını gizle
            $('#email').on('input', function() {
                $('#emailError').hide();
            });

            // Şifre alanında değişiklik olduğunda hata mesajını gizle
            $('#password').on('input', function() {
                $('#passwordError').hide();
            });
        });
    </script>
</body>
</html>
