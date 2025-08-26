<?php
session_start();

require_once 'repositories/database_repository.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // 6 haneli doğrulama kodu üret
    $token = rand(100000, 999999);

    // Kod ve e-postayı session'da sakla
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_token'] = $token;
    $_SESSION['token_expire'] = time() + (15 * 60); // 15 dakika geçerli

    // Mail başlığı ve içeriği
    $subject = "Şifre Sıfırlama Doğrulama Kodu";
    $message = "Merhaba,\n\nŞifre sıfırlama işlemi için doğrulama kodunuz: $token\n\nBu kod 15 dakika geçerlidir.";
    $headers = "From: no-reply@seninsiten.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Mail gönderimi
    if (mail($email, $subject, $message, $headers)) {
        header("Location: dogrula.html");
        exit;
    } else {
        echo "Kod gönderilemedi. Lütfen tekrar deneyin.";
    }
}
?>