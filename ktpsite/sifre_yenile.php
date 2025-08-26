<?php
session_start();

// Eğer kullanıcı doğrulama kodunu girmediyse erişimi engelle
if (!isset($_SESSION['reset_email'])) {
    header("Location: index.html");
    exit;
}

$mesaj = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeniSifre = $_POST['new_password'] ?? '';
    $tekrarSifre = $_POST['confirm_password'] ?? '';

    if (strlen($yeniSifre) < 6) {
        $mesaj = "Şifre en az 6 karakter olmalıdır.";
    } elseif ($yeniSifre !== $tekrarSifre) {
        $mesaj = "Şifreler uyuşmuyor.";
    } else {
        // Burada gerçek uygulamada:
        // Veritabanında kullanıcının emailine göre şifreyi güncelle
        // Örnek:
        // $email = $_SESSION['reset_email'];
        // $hashed = password_hash($yeniSifre, PASSWORD_DEFAULT);
        // UPDATE users SET password = $hashed WHERE email = $email;

        // Şimdilik sadece mesaj gösteriyoruz
        $mesaj = "Şifreniz başarıyla güncellendi!";

        // Oturumdaki reset verilerini temizle
        unset($_SESSION['reset_email'], $_SESSION['reset_token'], $_SESSION['token_expire']);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Şifre Yenile</title>
<style>
    :root {
        --main-color: #00174A;
        --light-bg: #f4f6fc;
    }
    body {
        font-family: Arial, sans-serif;
        background: var(--light-bg);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 400px;
        text-align: center;
        box-sizing: border-box;
    }
    h2 {
        color: var(--main-color);
        margin-bottom: 20px;
    }
    input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
        box-sizing: border-box;
    }
    input:focus {
        border-color: var(--main-color);
        outline: none;
    }
    button {
        width: 100%;
        background: var(--main-color);
        color: white;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }
    button:hover {
        background: #003088;
    }
    .message {
        font-size: 14px;
        margin-bottom: 12px;
        color: red;
    }
    .success {
        color: green;
    }
</style>
</head>
<body>
<?php
require_once 'repositories/database_repository.php';
?>
<div class="container">
    <h2>Yeni Şifrenizi Belirleyin</h2>

    <?php if ($mesaj): ?>
        <div class="message <?= ($mesaj === "Şifreniz başarıyla güncellendi!") ? "success" : "" ?>">
            <?= htmlspecialchars($mesaj) ?>
        </div>
    <?php endif; ?>

    <?php if ($mesaj !== "Şifreniz başarıyla güncellendi!"): ?>
    <form method="POST" novalidate>
        <input type="password" name="new_password" placeholder="Yeni Şifre" required minlength="6" />
        <input type="password" name="confirm_password" placeholder="Yeni Şifre (Tekrar)" required minlength="6" />
        <button type="submit">Şifreyi Güncelle</button>
    </form>
    <?php else: ?>
        <p><a href="index.html" style="color:var(--main-color); text-decoration:none;">Giriş sayfasına dön</a></p>
    <?php endif; ?>
</div>
</body>
</html>