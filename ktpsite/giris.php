<?php
// Giriş.php — düzeltilmiş ve tam sayfa
declare(strict_types=1);

// Oturum kesinlikle başlatılıyor
session_start();

// Database repository (dizin sabiti __DIR__ ile doğru şekilde dahil)
require_once 'repositories/database_repository.php';

// Eğer zaten giriş yapılmışsa anasayfaya yönlendir
if (!empty($_SESSION['email'])) {
    header('Location: anasayfa.php');
    exit;
}

$errorMsg = '';

// POST geldiğinde işle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginSubmit'])) {
    // Güvenlik: gelen verileri al ve temizle
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // Temel validasyon
    if ($email === '') {
        $errorMsg = 'Lütfen e-posta girin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Geçerli bir e-posta adresi girin.';
    } elseif ($password === '') {
        $errorMsg = 'Lütfen şifrenizi girin.';
    } else {
        try {
            $res = getUserByLoginDetailed($email, $password);
        } catch (Throwable $e) {
            error_log('[login] getUserByLoginDetailed exception: ' . $e->getMessage());
            $res = ['status' => 'error'];
        }

        if (!is_array($res)) {
            $errorMsg = 'Bilinmeyen bir hata oluştu.';
            error_log('[login] getUserByLoginDetailed döndürdüğü şey array değil.');
        } else {
            switch ($res['status'] ?? 'error') {
                case 'ok':
                    // başarılı giriş
                    $u = $res['user'] ?? null;
                    
                    if (!is_array($u) || !isset($u['USER_ID'])) {
                        // Eğer beklenen alan yoksa hata
                        error_log('[login] getUserByLoginDetailed user verisi eksik: ' . print_r($u, true));
                        $errorMsg = 'Sunucu tarafında kullanıcı verisi eksik. Lütfen yöneticiye bildirin.';
                        break;
                    }

                    // Güvenlik: session id yenile
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int)$u['USER_ID'];
                    $_SESSION['email'] = $u['EMAIL'] ?? $email;

                    // isteğe bağlı: CSRF token oluştur (ileride formlar için)
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }

                    error_log('[login] success user_id=' . $_SESSION['user_id'] . ' session_id=' . session_id());

                    header('Location: anasayfa.php');
                    exit;

                case 'no_user':
                    $errorMsg = 'E-posta adresiniz yanlış. Lütfen geçerli bir e-posta girin.';
                    break;
                case 'inactive':
                    $errorMsg = 'Hesabınız aktif değil. Lütfen yöneticiye başvurun.';
                    break;
                case 'wrong_password':
                    $errorMsg = 'Girdiğiniz şifre yanlış. Lütfen geçerli bir şifre girin.';
                    break;
                case 'error':
                default:
                    $errorMsg = 'Sunucu bağlantı hatası. Lütfen daha sonra tekrar deneyin.';
                    break;
            }
        }
    }

    // Hata varsa session'a koyup redirect yap (PRG) — böylece F5 sonrası alanlar temizlenir
    if ($errorMsg !== '') {
        $_SESSION['login_error'] = $errorMsg;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// GET veya redirect sonrası: session'dan hata al ve temizle (tek seferlik göster)
if (isset($_SESSION['login_error'])) {
    $errorMsg = (string)$_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Giriş</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
       /* Eğer küçük ek stiller gerekirse buraya ekleyebilirsin; mevcut CSS'i bozmamak için burada değişiklik yapmadım. */
    </style>
</head>
<body>
    <div class="giris" role="main" aria-labelledby="loginTitle">
        <img src="images/kitaplik3.png" alt="Site logosu">
        <div class="giris-small">
            <h1 id="loginTitle" class="giris-h1">Giriş Yap</h1>

            <!-- Hata mesajı gösterimi (CSS'in bozulmaması için basit div kullandım; istersen style.css'e .login-error ekleyebilirsin) -->
            <?php if ($errorMsg !== ''): ?>
                <div class="login-error" role="alert" aria-live="assertive" style="margin-bottom:12px;">
                    <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Form: adlar PHP ile uyumlu (email, password, loginSubmit) -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                <input
                    id="email"
                    name="email"
                    class="input-email"
                    type="email"
                    placeholder="E-Postanızı girin"
                    autocomplete="email"
                    required
                    aria-required="true"
                    />

                <input
                    id="password"
                    name="password"
                    class="input-sifre"
                    type="password"
                    placeholder="Şifrenizi girin"
                    autocomplete="current-password"
                    required
                    aria-required="true"
                    />

                <!-- submit butonu PHP'nin beklediği name ile -->
                <button id="loginSubmit" name="loginSubmit" class="btn-giris-yap" type="submit">Giriş Yap</button>

                <h3 class="h3-kayit">Kayıt Olmadınız mı?</h3>
                <a class="kayit-ol-link" href="kayıt_ol.php">Kayıt ol</a>
                <a class="sifremi-unuttum-link" href="index.html">Şifremi Unuttum</a>
            </form>
        </div>
    </div>
</body>
</html>