<?php
session_start();
require_once 'repositories/database_repository.php';

$profileName = 'Ad - Soyad';
$profileEmail = 'E-mail';
$profileDate = 'Tarih / Saat';

// Oturum zorunluluğu (opsiyonel: giriş yoksa login'e yönlendir)
if (empty($_SESSION['user_id']) && empty($_SESSION['email'])) {
    // header('Location: giris.php'); exit; // İstersen aktif et
}

// Önce user_id ile dene, olmazsa email ile fallback yap
$user = null;
if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $user = getUserProfileById($uid);
}
if (!$user && !empty($_SESSION['email'])) {
    $user = getUserProfileByEmail((string)$_SESSION['email']);
}

if (is_array($user)) {
    if (!empty($user['NAME'])) {
        $profileName = (string)$user['NAME'];
    }
    if (!empty($user['EMAIL'])) {
        $profileEmail = (string)$user['EMAIL'];
    }
    if (array_key_exists('CREATION_DATE', $user) && $user['CREATION_DATE'] !== null && $user['CREATION_DATE'] !== '') {
        $profileDate = (string)$user['CREATION_DATE'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F8 Kütüphane - Profil</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <style>
        .container {
  max-width: 600px;
  background: #ffffff;
  margin: 50px auto;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 6px 15px rgba(0,23,74,0.1);
  text-align: center;
}

.logo {
  max-width: 150px;
  margin: 0 auto 20px auto;
  display: block;
}

h1 {
  margin-bottom: 25px;
  font-weight: 700;
  font-size: 2.5rem;
  letter-spacing: 1.2px;
}

.info {
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 30px;
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-top: 20px;
}

.social-links a {
  text-decoration: none;
  color: #00174a;
  font-weight: 600;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color 0.3s ease;
}

.social-links a:hover {
  color: #6a8bae;
}

.icon {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

/* Footer ÖZEL classları */

.main-footer {
  background-color: #001f4d;
  color: #ffffff;
  padding: 40px 20px 20px;
  font-size: 14px;
  margin-top: 60px;
  font-family: system-ui;
}

.main-footer__content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
  gap: 30px;
}

.main-footer__section {
  flex: 1 1 280px;
}

.main-footer__section h4 {
  margin-bottom: 12px;
  font-size: 16px;
  color: #bbdefb;
}

.main-footer__section p,
.main-footer__section a {
  margin: 4px 0;
  color: #e3f2fd;
  text-decoration: none;
}

.main-footer__section a:hover {
  color: #ffffff;
}

.main-footer__social-links a {
  display: inline-flex;
  align-items: center;
  margin: 6px 12px 6px 0;
  font-weight: 500;
  color: #e3f2fd;
  transition: color 0.3s ease;
}

.main-footer__social-links a:hover {
  color: #ffffff;
}

.social-icon {
  width: 20px;
  height: 20px;
  margin-right: 6px;
  fill: currentColor;
}

.main-footer__bottom {
  text-align: center;
  margin-top: 30px;
  border-top: 1px solid #446;
  padding-top: 10px;
  font-size: 13px;
  color: #ccc;
}
    </style>

    <div class="navbar-image">
        <div class="navbar-image-div">
             <img class="navbar-image-img" src="images/F8-logo.png" alt="">
             <h1 class="navbar-image-h1">Kütüphane</h1>
        </div>
    </div>
    <nav class="navbar">
        <img class="navbar-img" src="images/F8-logo.png" alt="">
        <ul class="navbar-ul">
            <li class="navbar-ul-li"><a style="color: white; text-decoration: none;" href="anasayfa.php">Ana Sayfa</a></li>
            <li class="navbar-ul-li"><a style="color: white; text-decoration: none;" href="profil.php">Profil</a></li>
            <li class="navbar-ul-li"><a style="color: white; text-decoration: none;" href="iletişim.html">İletişim</a></li>
            <li class="navbar-ul-li"><a style="color: white; text-decoration: none;" href="hakkimizda.php">Hakkımızda</a></li>
            <li class="navbar-ul-li"><a style="color: white; text-decoration: none;" href="bildirimler.html">Bildirimler</a></li>
        </ul>
        <div class="navbar-profil-simge">
          <img src="images/kullanici.jpg" alt="kullanici.jpg">
        </div>
        <a class="navbar-cikis-link" href="cikis.php"><i style="color: white; margin-left: 30px; margin-right: 20px;" class="fas fa-sign-out-alt fa-3x"></i></a>
    </nav>


    <div class="profil-body">
        <div class="profil">
          <div class="profil-photo">
            <img class="profil-photo-img" src="images/kullanici.jpg">
          </div>
          <div class="profil-kullanici">
            <h2 class="profil-kullanici-name"><?php echo htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2 class="profil-kullanici-mail"><?php echo htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2 class="profil-kullanici-tarih"><?php echo htmlspecialchars($profileDate, ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="profil-sayilar-div">
              <div class="profil-sayi">6 Favori</div>
              <div class="profil-sayi">8 Okunan</div>
            </div>
          </div>
        </div>
        <?php
// Favoriler bölümünü dolduran kod
// Gereksinimler: session açık olmalı, $bmUserId, getConnection(), getBookById(), e(), $placeholderImage değişkenleri mevcut olmalı.

echo '<div class="profil-2">'; // <<-- buraya eklendi

echo '<div class="profil-favoriler">';
echo '<h1 class="profil-sayilar-baslik">Favorilerim</h1>';

if (empty($bmUserId)) {
    echo '<p>Lütfen favorileri görmek için <a href="giris.php">giriş</a> yapın.</p>';
} else {
    try {
        $conn = getConnection();
        // Kullanıcının favori kayıtlarını al
        $sql = "SELECT BOOK_ID FROM F8LIB_FAVORITES WHERE USER_ID = :uid AND IS_FAVORITE = 1";
        $stid = @oci_parse($conn, $sql);
        if ($stid) {
            oci_bind_by_name($stid, ':uid', $bmUserId);
            oci_execute($stid);
            $favIds = [];
            while (($row = oci_fetch_assoc($stid)) !== false) {
                // BOOK_ID sütun adı veritabanında büyük/küçük farklı olabilir; normalize et
                $favIds[] = $row['BOOK_ID'] ?? ($row['BOOKId'] ?? ($row['BOOK_Id'] ?? null));
            }
            oci_free_statement($stid);
        } else {
            $favIds = [];
        }

        if (empty($favIds)) {
            echo '<div class="yorumkutusu-no-reviews"><p>Henüz favori eklemediniz.</p></div>';
        } else {
            // Her favori için kitap bilgilerini alıp f-card oluştur
            foreach ($favIds as $bid) {
                $bid = (int)$bid;
                if ($bid <= 0) continue;
                // getBookById fonksiyonunu kullan (repositories/database_repository.php içinde tanımlı)
                $book = null;
                if (function_exists('getBookById')) {
                    $book = getBookById($bid);
                } else {
                    // Eğer getBookById yoksa doğrudan sorgula (önerilen: getBookById kullan)
                    $book = null;
                    $bSt = @oci_parse($conn, "SELECT BOOK_NAME, BOOK_IMAGE FROM F8LIB_BOOKS WHERE BOOK_ID = :bid");
                    if ($bSt) {
                        oci_bind_by_name($bSt, ':bid', $bid);
                        if (@oci_execute($bSt)) {
                            $book = oci_fetch_assoc($bSt) ?: null;
                        }
                        @oci_free_statement($bSt);
                    }
                }

                if (empty($book)) continue;

                $img = e($book['BOOK_IMAGE'] ?? $book['Book_Image'] ?? $placeholderImage ?? 'images/placeholder.png');
                $name = e($book['BOOK_NAME'] ?? $book['Book_Name'] ?? 'Başlıksız');

                echo '<div class="f-card">';
                echo    '<img class="f-img" src="'. $img .'" alt="'. $name .'">';
                echo    '<div class="f-div">';
                echo        '<h2 class="f-h2">'. $name .'</h2>';
                echo    '</div>';
                echo '</div>';
            }
        }

        @oci_close($conn);
    } catch (Throwable $ex) {
        error_log('Favoriler çekilirken hata: '.$ex->getMessage());
        echo '<div class="yorumkutusu-no-reviews"><p>Favoriler yüklenirken bir hata oluştu.</p></div>';
    }
}

echo '</div>'; // .profil-favoriler

// OKUDUKLARIM bölümü: şu anda veri kaynağı belirtilmediği için placeholder gösteriyorum.
// Eğer okuduklarımı da aynı tablodan (ör. IS_READ = 1) çekmek istersen, benzer sorgu eklerim.
echo '<div class="profil-okunanlar">';
echo '<h1 class="profil-sayilar-baslik">Okuduklarım</h1>';
echo '<div class="yorumkutusu-no-reviews"><p>Henüz okunan kitap verisi yok.</p></div>';
echo '</div>';

echo '</div>'; // <<-- profil-2 divi kapatıldı
?>

    </div>
    

    <footer class="main-footer">
  <div class="main-footer__content">
    
    <div class="main-footer__section">
      <h4>F8 Kütüphane Sistemi</h4>
      <p>Adres: Piri Paşa, Ütücü Ferhat Sk. No:3, 34445 Beyoğlu/İstanbul</p>
      <p>Telefon: (0212) 369 04 99</p>
      <p>E-posta: <a href="mailto:iletisim@sirketadi.com">iletisim@sirketadi.com</a></p>
    </div>

    <div class="main-footer__section">
      <h4>Kütüphane Sorumlusu</h4>
      <p>Ayşe Yılmaz</p>
      <p><a href="mailto:ayse.yilmaz@sirketadi.com">ayse.yilmaz@sirketadi.com</a></p>
      <p>Dahili No: 1234</p>
      <p>Ofis: 3. Kat, Oda 312</p>
    </div>

    <div class="main-footer__section">
      <h4>Bizi Takip Edin</h4>
      <div class="main-footer__social-links">
        <a href="https://www.instagram.com/f8bilisim/" target="_blank" aria-label="Instagram">
          <svg class="social-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm8.9 3.86a1.1 1.1 0 110 2.2 1.1 1.1 0 010-2.2zM12 7a5 5 0 110 10 5 5 0 010-10z"/></svg>
          Instagram
        </a>
        <a href="https://tr.linkedin.com/company/f8bilisim" target="_blank" aria-label="LinkedIn">
          <svg class="social-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M4.98 3.5a2.5 2.5 0 11-.001 5.001 2.5 2.5 0 01.001-5.001zM3 9h4v12H3zm11.5 0c-2.2 0-2.5 1.2-2.5 1.8v1.8h-4v12h4v-9c0-.5.4-1 1-1s1 .5 1 1v9h4v-10c0-2.2-1.2-3-3.5-3z"/></svg>
          LinkedIn
        </a>
        <a href="https://x.com/F8Bilisim" target="_blank" aria-label="Twitter">
          <svg class="social-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M8 19c7.732 0 11.955-6.406 11.955-11.955 0-.182 0-.364-.013-.545A8.54 8.54 0 0022 4.557a8.19 8.19 0 01-2.356.646 4.11 4.11 0 001.804-2.27 8.203 8.203 0 01-2.605.996 4.1 4.1 0 00-6.987 3.739 11.635 11.635 0 01-8.447-4.28 4.068 4.068 0 001.27 5.462 4.073 4.073 0 01-1.859-.512v.05a4.1 4.1 0 003.29 4.018 4.09 4.09 0 01-1.853.07 4.106 4.106 0 003.83 2.85A8.233 8.233 0 012 18.13a11.616 11.616 0 006.29 1.84"/></svg>
          Twitter
        </a>
        <a href="https://www.facebook.com/f8bilisim" target="_blank" aria-label="Facebook">
          <svg class="social-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 22v-8.08h2.7l.4-3.15h-3.1v-2.02c0-.9.25-1.52 1.54-1.52h1.65v-2.8a21.57 21.57 0 00-2.35-.12c-2.32 0-3.9 1.4-3.9 3.96v2.2H8v3.15h2.7V22h2.8z"/></svg>
          Facebook
        </a>
      </div>
    </div>
  </div>

  <div class="main-footer__bottom">
    <p>&copy; 2025 F8 Bilişim ve Danışmanlık Hizmetleri Ltd. Şti. - Tüm hakları saklıdır.</p>
  </div>
</footer>



</body>
</html>