<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_photo'];
    $target_dir = "uploads/";
    $target_file = $target_dir . $user_id . ".jpg";

    // Dosya türü kontrolü
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowed_types)) {
        die("Sadece JPEG ve PNG türündeki dosyalar kabul edilir.");
    }

    // Dosya boyutu kontrolü (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        die("Dosya boyutu 5MB'yi aşamaz.");
    }

    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        header("Location: profile.php");
    } else {
        echo "Dosya yüklenirken bir hata oluştu.";
    }
}
