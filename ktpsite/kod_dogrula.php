<?php
session_start();

require_once 'repositories/database_repository.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $girilenKod = trim($_POST['token']);

    if (!isset($_SESSION['reset_token']) || !isset($_SESSION['token_expire'])) {
        die("Kod bulunamadı. Lütfen tekrar deneyin.");
    }

    if (time() > $_SESSION['token_expire']) {
        die("Kodun süresi doldu. Lütfen tekrar deneyin.");
    }

    if ($girilenKod == $_SESSION['reset_token']) {
        echo "Kod doğrulandı! Şimdi şifrenizi yenileyebilirsiniz.";
        // Burada şifre yenileme sayfasına yönlendirebilirsin
    } else {
        echo "Kod hatalı. Lütfen tekrar deneyin.";
    }
}
?>
