<?php
session_start(); // Oturumu başlat
session_unset(); // Tüm oturum değişkenlerini temizle
session_destroy(); // Oturumu tamamen yok et

// Giriş sayfasına yönlendir
header("Location: giris.php");
exit;
?>