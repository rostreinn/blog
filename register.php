<?php
session_start();

// Veritabanı bağlantısı
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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullaniciadi = trim($_POST['kullaniciadi']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Doğrulama
    if (empty($kullaniciadi) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tüm alanları doldurmanız gerekiyor.';
    } elseif ($password !== $confirm_password) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        // E-posta kontrolü
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM uyeler WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $email_exists = $stmt->fetchColumn();

        if ($email_exists) {
            $error = 'Bu e-posta adresi zaten kayıtlı.';
        } else {
            // Şifreyi hashleme
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Üye kaydı
            $stmt = $pdo->prepare("INSERT INTO uyeler (kullaniciadi, email, password) VALUES (:kullaniciadi, :email, :password)");
            $stmt->execute([
                'kullaniciadi' => $kullaniciadi,
                'email' => $email,
                'password' => $hashed_password,
            ]);

            $success = 'Kayıt başarılı! Giriş yapmak için <a href="login.php">buraya tıklayın</a>.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .strength-meter {
            height: 5px;
            width: 100%;
            background-color: #ddd;
        }
        .strength-meter div {
            height: 100%;
        }
        .strength-weak {
            background-color: red;
        }
        .strength-medium {
            background-color: yellow;
        }
        .strength-strong {
            background-color: green;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Kayıt Ol</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form id="registerForm" method="POST" action="register.php">
            <div class="mb-3">
                <label for="kullaniciadi" class="form-label">Kullanıcı Adı:</label>
                <input type="text" class="form-control" id="kullaniciadi" name="kullaniciadi" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-posta:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre:</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div id="password-strength" class="strength-meter">
                    <div></div>
                </div>
                <small id="passwordHelp" class="form-text text-muted">Şifreniz en az 8 karakter, bir büyük harf, bir küçük harf ve bir rakam içermelidir.</small>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Şifreyi Onayla:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
        </form>
        <p class="text-center mt-3">Zaten bir hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Şifre gücü ölçer
            $('#password').on('input', function() {
                var password = $(this).val();
                var strength = 0;
                
                // En az 8 karakter
                if (password.length >= 8) strength++;
                // Bir büyük harf
                if (/[A-Z]/.test(password)) strength++;
                // Bir küçük harf
                if (/[a-z]/.test(password)) strength++;
                // Bir rakam
                if (/\d/.test(password)) strength++;

                // Güç göstergesi
                var strengthMeter = $('#password-strength div');
                strengthMeter.removeClass('strength-weak strength-medium strength-strong');

                if (strength === 1) {
                    strengthMeter.addClass('strength-weak').css('width', '25%');
                } else if (strength === 2) {
                    strengthMeter.addClass('strength-medium').css('width', '50%');
                } else if (strength >= 3) {
                    strengthMeter.addClass('strength-strong').css('width', '100%');
                } else {
                    strengthMeter.css('width', '0');
                }
            });

            // Şifre onay kontrolü
            $('#confirm_password').on('input', function() {
                var password = $('#password').val();
                var confirmPassword = $(this).val();
                if (confirmPassword !== password) {
                    $(this).get(0).setCustomValidity('Şifreler eşleşmiyor.');
                } else {
                    $(this).get(0).setCustomValidity('');
                }
            });

            // Formun submit edilmeden önce tüm alanların doğrulama işlemi
            $('#registerForm').on('submit', function(event) {
                var password = $('#password').val();
                var confirmPassword = $('#confirm_password').val();

                if (password !== confirmPassword) {
                    event.preventDefault();
                    alert("Şifreler eşleşmiyor!");
                }
            });
        });
    </script>
</body>
</html>
