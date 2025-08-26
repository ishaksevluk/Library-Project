<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'repositories/database_repository.php';

/* XSS helper */
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* CSRF token hazırla */
if (!isset($_SESSION['bm_csrf_token'])) {
    $_SESSION['bm_csrf_token'] = bin2hex(random_bytes(24));
}
$bmCsrf = $_SESSION['bm_csrf_token'];

/* bookId alma (POST 'kitap-detay' veya GET 'id') */
$bookId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kitap-detay'])) {
    $bookId = (int)$_POST['kitap-detay'];
} elseif (isset($_GET['id'])) {
    $bookId = (int)$_GET['id'];
}

/* Varsayılan değerler */
$notFound = true;
$book = null;
if ($bookId && $bookId > 0) {
    $book = getBookById($bookId);
    $notFound = $book === null;
}

/* Değerleri ayarla (kullanıcı tarafından sağlanan alan isimleri tabloya göre değişebilir) */
$placeholderImage = 'images/placeholder.png';

$title       = $notFound ? 'Kitap Bulunamadı' : (string) ($book['BOOK_NAME'] ?? ($book['Book_Name'] ?? 'Başlıksız'));
$imgSrc      = $notFound ? $placeholderImage : (string) ($book['BOOK_IMAGE'] ?? ($book['Book_Image'] ?? $placeholderImage));
$categoryId = $notFound ? '-' : (string) ($book['CATEGORY_ID'] ?? ($book['Category_Id'] ?? '-'));
$authorId   = $notFound ? '-' : (string) ($book['AUTHOR_ID'] ?? ($book['Author_Id'] ?? '-'));
$publisher   = $notFound ? '-' : (string) ($book['PUBLISHER'] ?? ($book['Publisher'] ?? '-'));
$publishYear = $notFound ? '-' : (string) ($book['PUBLISH_YEAR'] ?? ($book['Publish_Year'] ?? '-'));
$pageCount   = $notFound ? '-' : (string) ($book['PAGE_COUNT'] ?? ($book['Page_Count'] ?? '-'));
$summary     = $notFound ? '-' : (string) ($book['BOOK_SUMMARY'] ?? ($book['Book_Summary'] ?? '-'));
$isbn        = $notFound ? '-' : (string) ($book['ISBN'] ?? ($book['Isbn'] ?? '-'));

if ($imgSrc === '' || $imgSrc === '-') $imgSrc = $placeholderImage;

/* Eğer kullanıcı giriş yapmışsa session user_id mevcut olmalı */
$bmUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

/* Eğer sayfada POST ile ödünç sonucu döndürmek istersen burada değil, odunc_al.php kullanacağız */

/* Yorumları veritabanından çek */
$reviews = [];
if ($bookId && $bookId > 0) {
    try {
        $conn = getConnection();
        $sqlReviews = "SELECT USER_ID, BOOK_ID, RATING, REVIEW_COMMENT FROM F8LIB_BOOK_REVIEWS WHERE BOOK_ID = :bookId ORDER BY USER_ID";
        $stidReviews = oci_parse($conn, $sqlReviews);
        oci_bind_by_name($stidReviews, ':bookId', $bookId);
        oci_execute($stidReviews);
        
        while (($row = oci_fetch_assoc($stidReviews)) !== false) {
            $reviews[] = $row;
        }
        oci_free_statement($stidReviews);
        oci_close($conn);
    } catch (Exception $e) {
        // Hata durumunda boş array ile devam et
        error_log('Yorumlar çekilirken hata: ' . $e->getMessage());
    }
}

/* Escaped değerler */
$bookIdEsc = e($bookId ?? '');
$bmCsrfEsc = e($bmCsrf);
?>
<?php
// Favori başlangıç durumu
$initialIsFavorite = 0;
try {
    if (!empty($bmUserId) && !empty($bookId)) {
        $connFav = getConnection();
        $sqlFav = "SELECT IsFavorite FROM F8LIB_FAVORITES WHERE USER_ID = :uid AND BOOK_ID = :bid";
        $stidFav = @oci_parse($connFav, $sqlFav);
        if ($stidFav) {
            oci_bind_by_name($stidFav, ':uid', $bmUserId);
            oci_bind_by_name($stidFav, ':bid', $bookId);
            if (@oci_execute($stidFav)) {
                $rowFav = oci_fetch_array($stidFav, OCI_ASSOC + OCI_RETURN_NULLS);
                if ($rowFav && isset($rowFav['ISFAVORITE']) && (string)$rowFav['ISFAVORITE'] === '1') {
                    $initialIsFavorite = 1;
                }
            }
            oci_free_statement($stidFav);
        }
        oci_close($connFav);
    }
} catch (Exception $e) {
    // sessiz geç
}
?>







<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F8 Kütüphane - Kitap Bilgileri</title>
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

/* YORUM KUTUSU STİLLERİ */

.yorumkutusu-card {
      box-sizing: border-box;
      width: 1040px;
      margin: 170px auto 18px auto;
      padding: 20px;
      background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
      border-radius: 14px;
      box-shadow: 0 8px 22px rgba(12, 18, 33, 0.08), inset 0 1px 0 rgba(255,255,255,0.6);
      border: 1px solid rgba(0,0,0,0.06);
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .yorumkutusu-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .yorumkutusu-title {
      font-size: 16px;
      font-weight: 700;
      color: #0f1a33;
    }

    .yorumkutusu-subtitle {
      font-size: 13px;
      color: #6b7280;
      margin-top: 2px;
      margin-left: 9px;
    }

    /* Yorum listesi kapsayıcısı */
    .yorumkutusu-list-wrap {
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid rgba(8,12,20,0.04);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
    }

    .yorumkutusu-list {
      max-height: 280px; /* kaydırma alanı */
      overflow-y: auto;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      background: linear-gradient(180deg, rgba(250,250,252,1) 0%, rgba(248,249,250,1) 100%);
    }

    /* Her bir yorum kartı */
    .yorumkutusu-item {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 10px;
      background: #fff;
      border-radius: 10px;
      border: 1px solid rgba(10,14,26,0.04);
      box-shadow: 0 6px 14px rgba(12,18,33,0.04);
    }

    .yorumkutusu-item-avatar {
      width: 44px;
      height: 44px;
      border-radius: 8px;
      background: #eef2ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #243b71;
      flex: 0 0 44px;
    }

    .yorumkutusu-item-body {
      flex: 1 1 auto;
      min-width: 0;
    }

    .yorumkutusu-item-head {
      display: flex;
      gap: 8px;
      align-items: baseline;
      margin-bottom: 6px;
    }

    .yorumkutusu-item-name {
      font-weight: 700;
      color: #09102a;
      font-size: 14px;
    }

    .yorumkutusu-item-time {
      color: #8b93a7;
      font-size: 12px;
    }

    .yorumkutusu-item-text {
      font-size: 14px;
      color: #12202f;
      line-height: 1.45;
      word-break: break-word;
    }

    /* Yorum eylem çubuğu (beğen, cevap gibi) */
    .yorumkutusu-actions {
      margin-top: 8px;
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .yorumkutusu-btn {
      appearance: none;
      border: 0;
      background: linear-gradient(180deg,#0b2b6b,#0e2c6d);
      color: #fff;
      padding: 6px 10px;
      border-radius: 8px;
      box-shadow: 0 6px 12px rgba(11,43,107,0.18);
      font-size: 13px;
      cursor: pointer;
      transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
    }

    .yorumkutusu-btn:active { transform: translateY(1px); }
    .yorumkutusu-btn:hover { opacity: 0.96; }

    .yorumkutusu-like {
      background: linear-gradient(180deg,#ffffff,#e8f0ff);
      color: #0b2b6b;
      border: 1px solid rgba(11,43,107,0.08);
      box-shadow: none;
      padding: 6px 8px;
    }

    /* Yorum gönder alanı */
    .yorumkutusu-form {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      margin-top: 14px;
    }

    .yorumkutusu-input-wrap {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .yorumkutusu-input {
      width: 100%;
      min-height: 48px;
      resize: vertical;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid rgba(10,14,26,0.06);
      background: #fff;
      font-size: 13px;
      box-shadow: 0 6px 14px rgba(12,18,33,0.04) inset;
      outline: none;
      box-sizing: border-box;
      transition: box-shadow .12s ease, border-color .12s ease;
      font-family: inherit;
    }

    .yorumkutusu-input:focus {
      border-color: rgba(11,43,107,0.14);
      box-shadow: 0 6px 18px rgba(11,43,107,0.06);
    }

    .yorumkutusu-submit {
      flex: 0 0 110px;
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .yorumkutusu-send-btn {
      width: 100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 0;
      font-weight: 700;
      background: linear-gradient(180deg,#112a64,#0b2150);
      color: #fff;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(11,43,107,0.18);
    }

    .yorumkutusu-send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      box-shadow: none;
    }

         .yorumkutusu-meta {
       font-size: 12px;
       color: #7b8292;
     }

     /* Yorum yoksa gösterilecek mesaj */
     .yorumkutusu-no-reviews {
       text-align: center;
       padding: 40px 20px;
       color: #6b7280;
     }

     .yorumkutusu-no-reviews p {
       font-size: 14px;
       margin: 0;
       font-style: italic;
     }

     /* Rating yıldızları için stil */
     .yorumkutusu-item-rating {
       margin-left: auto;
     }

     .yorumkutusu-item-rating i {
       font-size: 12px;
       margin-left: 2px;
     }

     /* Rating seçimi için stiller */
     .yorumkutusu-rating-select {
       margin-bottom: 10px;
     }

     .yorumkutusu-rating-select label {
       font-size: 13px;
       color: #374151;
       margin-right: 10px;
       font-weight: 500;
     }

     .rating-stars {
       display: inline-flex;
       gap: 2px;
     }

     .rating-stars input[type="radio"] {
       display: none;
     }

     .star-label {
       cursor: pointer;
       font-size: 18px;
       color: #d1d5db;
       transition: color 0.2s ease;
     }

           .star-label:hover,
      .star-label:hover ~ .star-label {
        color: #fbbf24;
      }

      .rating-stars input[type="radio"]:checked ~ .star-label {
        color: #fbbf24;
      }

      .rating-stars input[type="radio"]:checked + .star-label {
        color: #fbbf24;
      }

     /* Giriş yapmamış kullanıcılar için uyarı */
     .yorumkutusu-login-required {
       text-align: center;
       padding: 20px;
       background: #f8f9fa;
       border-radius: 8px;
       border: 1px solid #e9ecef;
     }

     .yorumkutusu-login-required p {
       margin: 0;
       color: #6c757d;
       font-size: 14px;
     }

     .yorumkutusu-login-required a {
       color: #007bff;
       text-decoration: none;
       font-weight: 500;
     }

     .yorumkutusu-login-required a:hover {
       text-decoration: underline;
     }

 /* YORUM KUTUSU STİLLERİ */


    </style>
    <div class="bildirims">
      <div class="bilgiler-bildirim">Başarıyla Ödünç Alındı</div>
    <div class="bilgiler-bildirim2">Rezervasyon Edildi!</div>
    </div>
    

    <!--NAVBAR-->
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

    

 
      <div class="bilgiler-body-div">
          
    <div class="bilgiler-kapsayici-div">
      <div class="bilgiler-img">
         <h1 class="bilgiler-img-h1" translation = "no"><?php echo e($notFound ? 'Kitap Bulunamadı' : $title); ?></h1>
         <img class="bilgiler-img-img" src="<?php echo e($imgSrc); ?>" alt="<?php echo e($title); ?>">
      </div>

      <div class="bilgiler-textler">
         <div class="bilgiler-textlerr">
             <?php
               $categoryName = '-';
               if ($categoryId !== '-' && $categoryId !== '') {
                   $c = getCategoryDescriptionById($categoryId);
                   if ($c !== null && $c !== '') $categoryName = $c;
               }
               $authorName = '-';
               if ($authorId !== '-' && $authorId !== '') {
                   $a = getAuthorNameById($authorId);
                   if ($a !== null && $a !== '') $authorName = $a;
               }
             ?>
             <h2 class="bilgiler-text">Tür: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($categoryName) . '</span>'; ?></h2>
             <h2 class="bilgiler-text">Yazar Adı: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($authorName) . '</span>'; ?></h2>
             <h2 class="bilgiler-text">Yayınevi: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($publisher ?: '-') . '</span>'; ?></h2>
             <h2 class="bilgiler-text">Basım Yılı: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($publishYear ?: '-') . '</span>'; ?></h2>
             <h2 class="bilgiler-text">Sayfa Sayısı: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($pageCount ?: '-') . '</span>'; ?></h2>
             <h2 class="bilgiler-text">Isbn: <?php echo '<span class="spans" style="color: #000000ff; -webkit-text-stroke: 1px #ffffffff;">' . e($isbn ?: '-') . '</span>'; ?></h2>

             <div class="bilgiler-text-buttons">
                 <form id="odunc-form" action="odunc_al.php" method="POST">
                     <input type="hidden" name="book_id" value="<?php echo $bookId; ?>">
                     <?php $_SESSION['book_id'] = $bookId; ?>
                     <?php
                        $isAvailable = true;
                        if (!$notFound) {
                            $state = strtolower(trim((string)($book['STATE'] ?? ($book['State'] ?? ''))));
                            if ($state === 'on reader') {
                                $isAvailable = false;
                            }
                        }
                        $btnStyle = $isAvailable ? '' : 'style="background-color: gray; pointer-events: none;"';
                        $btnDisableAttr = $isAvailable ? '' : 'disabled="disabled" aria-disabled="true"';
                     ?>
                     <button class="bilgiler-text-button-odunc" type="submit" <?php echo $btnStyle; ?> <?php echo $btnDisableAttr; ?>>Ödünç Al</button>
                 </form>
                 <form id="rezerve-form" action="rezerve.php" method="POST" style="display:inline;">
                     <input type="hidden" name="book_id" value="<?php echo $bookId; ?>">
                     <?php $_SESSION['book_id'] = $bookId; ?>
                     <?php
                        // Sayfa yüklenirken önce session kilidine bak, yoksa DB state kontrol et
                        $sessionLock = isset($_SESSION['reserve_lock'][(int)$bookId]) ? (int)$_SESSION['reserve_lock'][(int)$bookId] : 0;
                        $reserveState = ($bmUserId > 0 && $bookId) ? (getReservationState((int)$bmUserId, (int)$bookId) ?? '') : '';
                        $isReserveLocked = ($sessionLock === 1) || (strtolower(trim($reserveState)) !== '' && strtolower(trim($reserveState)) !== 'finish');
                        $reserveBtnAttrs = $isReserveLocked ? 'style="background-color: gray; pointer-events: none;" disabled="disabled" aria-disabled="true"' : '';
                     ?>
                     <button class="bilgiler-text-button-rezerve" type="submit" <?php echo $reserveBtnAttrs; ?>>Rezerve</button>
                 </form>
                 <button id="favori-btn" class="bilgiler-text-button-favori" type="button" aria-pressed="false">Favori</button>

                </div>
                <div id="odunc-result" aria-live="polite" style="position: relative; z-index: 9;"></div>
         </div>
      </div>
      <div class="bilgiler-summary">
          <p class="bilgiler-summary-p"><?php echo nl2br(e($summary ?: '-')); ?></p>
      </div>
    </div>
  </div>






          <div class="yorumkutusu-card" id="yorumkutusu-root">

    <div class="yorumkutusu-header">
      <div class="yorumkutusu-avatar"></div>
      <div>
        <div class="yorumkutusu-title"><i class="fa-solid fa-comments"></i> Yorumlar</div>
        <div class="yorumkutusu-subtitle"><span id="yorumkutusu-count"><?php echo count($reviews); ?></span> yorum</div>
      </div>
    </div>

    <div class="yorumkutusu-list-wrap">
             <div class="yorumkutusu-list" id="yorumkutusu-list">
         <?php if (empty($reviews)): ?>
           <!-- Yorum yoksa mesaj göster -->
           <div class="yorumkutusu-no-reviews">
             <p>Bu kitap için henüz yorum bulunmuyor. İlk yorumu siz yapın!</p>
           </div>
         <?php else: ?>
           <!-- Veritabanından gelen yorumları göster -->
           <?php foreach ($reviews as $review): ?>
             <?php
               // Kullanıcı adını al (USER_ID'den)
               $userName = 'Kullanıcı';
               try {
                 $connUser = getConnection();
                 $sqlUser = "SELECT NAME FROM F8LIB_USERS WHERE USER_ID = :userId";
                 $stidUser = oci_parse($connUser, $sqlUser);
                 oci_bind_by_name($stidUser, ':userId', $review['USER_ID']);
                 oci_execute($stidUser);
                 $userRow = oci_fetch_assoc($stidUser);
                 if ($userRow && isset($userRow['NAME'])) {
                   $userName = $userRow['NAME'];
                 }
                 oci_free_statement($stidUser);
                 oci_close($connUser);
               } catch (Exception $e) {
                 // Hata durumunda varsayılan isim kullan
               }
               
               // Kullanıcı adının baş harflerini al
               $initials = strtoupper(substr($userName, 0, 2));
               
               // Rating'i yıldız olarak göster
               $rating = (int)($review['RATING'] ?? 0);
               $stars = '';
               for ($i = 1; $i <= 5; $i++) {
                 if ($i <= $rating) {
                   $stars .= '<i class="fa-solid fa-star" style="color: #ffd700;"></i>';
                 } else {
                   $stars .= '<i class="fa-regular fa-star" style="color: #ccc;"></i>';
                 }
               }
             ?>
             <div class="yorumkutusu-item">
               <div class="yorumkutusu-item-avatar"><?php echo e($initials); ?></div>
               <div class="yorumkutusu-item-body">
                 <div class="yorumkutusu-item-head">
                   <div class="yorumkutusu-item-name"><?php echo e($userName); ?></div>
                   <div class="yorumkutusu-item-rating"><?php echo $stars; ?></div>
                 </div>
                 <div class="yorumkutusu-item-text"><?php echo nl2br(e($review['REVIEW_COMMENT'] ?? '')); ?></div>
                 <div class="yorumkutusu-actions">
                   <button class="yorumkutusu-btn yorumkutusu-like"><i class="fa-solid fa-thumbs-up"></i></button>
                   <button class="yorumkutusu-btn yorumkutusu-like"><i class="fa-solid fa-thumbs-down"></i></button>
                   <button class="yorumkutusu-btn"><i class="fa-solid fa-reply"></i></button>
                 </div>
               </div>
             </div>
           <?php endforeach; ?>
         <?php endif; ?>
       </div>
    </div>

         <?php if ($bmUserId > 0): ?>
       <form class="yorumkutusu-form" id="yorumkutusu-form" onsubmit="return false;">
         <div class="yorumkutusu-input-wrap">
           <!-- Rating seçimi -->
           <div class="yorumkutusu-rating-select">
             <label>Değerlendirme:</label>
                           <div class="rating-stars">
                <input type="radio" name="rating" value="1" id="star1">
                <label for="star1" class="star-label"><i class="fa-solid fa-star"></i></label>
                <input type="radio" name="rating" value="2" id="star2">
                <label for="star2" class="star-label"><i class="fa-solid fa-star"></i></label>
                <input type="radio" name="rating" value="3" id="star3">
                <label for="star3" class="star-label"><i class="fa-solid fa-star"></i></label>
                <input type="radio" name="rating" value="4" id="star4">
                <label for="star4" class="star-label"><i class="fa-solid fa-star"></i></label>
                <input type="radio" name="rating" value="5" id="star5" checked>
                <label for="star5" class="star-label"><i class="fa-solid fa-star"></i></label>
              </div>
           </div>
           
           <textarea class="yorumkutusu-input" id="yorumkutusu-text" placeholder="Yorumunuzu yazın..." rows="3" maxlength="800"></textarea>
           <div class="yorumkutusu-submit">
             <button class="yorumkutusu-send-btn" id="yorumkutusu-send" type="button">Gönder</button>
           </div>
           <div class="yorumkutusu-meta">Lütfen saygılı yorumlar yapmaya özen gösterin.</div>
         </div>
       </form>
     <?php else: ?>
       <div class="yorumkutusu-login-required">
         <p><i class="fa-solid fa-info-circle"></i> Yorum yapabilmek için lütfen <a href="giris.php">giriş yapın</a>.</p>
       </div>
     <?php endif; ?>

  </div>

  <script>
    (function(){
      // Yorum sistemi JavaScript kodu
      const listEl = document.getElementById('yorumkutusu-list');
      const countEl = document.getElementById('yorumkutusu-count');
      const textInput = document.getElementById('yorumkutusu-text');
      const sendBtn = document.getElementById('yorumkutusu-send');

      // Yorum sayısını güncelle
      function updateCount(){
        const items = listEl.querySelectorAll('.yorumkutusu-item').length;
        countEl.textContent = items;
      }

      // HTML escape fonksiyonu
      function escapeHtml(str){
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
      }

       // Yeni yorum ekleme - veritabanına gönder
       sendBtn.addEventListener('click', async ()=>{
         const text = textInput.value.trim();
         if(!text){
           textInput.focus();
           return;
         }

         // Rating değerini al
         const ratingInput = document.querySelector('input[name="rating"]:checked');
         const rating = ratingInput ? parseInt(ratingInput.value) : 5;

         // Butonu devre dışı bırak
         sendBtn.disabled = true;
         sendBtn.textContent = 'Gönderiliyor...';

         try {
           // FormData oluştur
           const formData = new FormData();
           formData.append('book_id', '<?php echo (int)($bookId ?? 0); ?>');
           formData.append('rating', rating.toString());
           formData.append('review_comment', text);

           // Debug: Gönderilecek verileri console'da göster
           console.log('Gönderilecek veriler:', {
             book_id: '<?php echo (int)($bookId ?? 0); ?>',
             rating: rating,
             comment: text
           });

           // Yorum ekleme isteği gönder
           const response = await fetch('yorum_ekle.php', {
             method: 'POST',
             credentials: 'same-origin',
             body: formData
           });

           // Debug: Response detaylarını console'da göster
           console.log('Response status:', response.status);
           const responseText = await response.text();
           console.log('Response text:', responseText);

           let result;
           try {
             result = JSON.parse(responseText);
           } catch (e) {
             console.error('JSON parse hatası:', e);
             alert('Sunucudan geçersiz yanıt alındı. Lütfen tekrar deneyin.');
             return;
           }

           console.log('Parsed result:', result);

           if (result.ok) {
             // Başarılı - yeni yorumu UI'da göster
             const newItem = document.createElement('div');
             newItem.className = 'yorumkutusu-item';
             
             const userName = result.data.user_name || 'Kullanıcı';
             const initials = userName.split(' ').map(s=>s[0]).slice(0,2).join('').toUpperCase();
             
             // Rating yıldızlarını oluştur
             let stars = '';
             for (let i = 1; i <= 5; i++) {
               if (i <= rating) {
                 stars += '<i class="fa-solid fa-star" style="color: #ffd700;"></i>';
               } else {
                 stars += '<i class="fa-regular fa-star" style="color: #ccc;"></i>';
               }
             }
             
             newItem.innerHTML = `
               <div class="yorumkutusu-item-avatar">${escapeHtml(initials)}</div>
               <div class="yorumkutusu-item-body">
                 <div class="yorumkutusu-item-head">
                   <div class="yorumkutusu-item-name">${escapeHtml(userName)}</div>
                   <div class="yorumkutusu-item-rating">${stars}</div>
                 </div>
                 <div class="yorumkutusu-item-text">${escapeHtml(text)}</div>
                 <div class="yorumkutusu-actions">
                   <button class="yorumkutusu-btn yorumkutusu-like"><i class="fa-solid fa-thumbs-up"></i></button>
                   <button class="yorumkutusu-btn yorumkutusu-btn"><i class="fa-solid fa-thumbs-down"></i></button>
                   <button class="yorumkutusu-btn"><i class="fa-solid fa-reply"></i></button>
                 </div>
               </div>
             `;

             // Yeni yorumu listenin başına ekle
             listEl.insertBefore(newItem, listEl.firstChild);
             
             // Input'u temizle
             textInput.value = '';
             
             // Sayacı güncelle
             updateCount();
             
             // Scroll'u en üstte tut
             listEl.scrollTop = 0;

             // Başarı mesajı göster
             alert(result.msg);
           } else {
             // Hata durumu
             let errorMsg = 'Bilinmeyen hata';
             if (result.msg === 'login_required') {
               errorMsg = 'Yorum yapabilmek için giriş yapmanız gerekiyor.';
             } else if (result.msg === 'invalid_book_id') {
               errorMsg = 'Geçersiz kitap ID.';
             } else if (result.msg === 'review_comment_required') {
               errorMsg = 'Lütfen yorum yazın.';
             } else if (result.msg === 'review_comment_too_long') {
               errorMsg = 'Yorum çok uzun. Maksimum 800 karakter.';
             } else if (result.msg === 'invalid_rating') {
               errorMsg = 'Geçersiz değerlendirme.';
             } else if (result.msg === 'database_error') {
               errorMsg = 'Veritabanı hatası. Lütfen tekrar deneyin.';
             } else if (result.msg === 'server_error') {
               errorMsg = 'Sunucu hatası. Lütfen tekrar deneyin.';
             } else {
               errorMsg = result.msg;
             }
             alert('Hata: ' + errorMsg);
           }
         } catch (error) {
           console.error('Yorum ekleme hatası:', error);
           alert('Yorum eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
         } finally {
           // Butonu tekrar aktif et
           sendBtn.disabled = false;
           sendBtn.textContent = 'Gönder';
         }
       });

      // Beğen butonları için event delegation
      listEl.addEventListener('click', (ev)=>{
        const btn = ev.target.closest('.yorumkutusu-btn');
        if(!btn) return;
        
        if(btn.classList.contains('yorumkutusu-like')){
          // Beğen toggle
          if(btn.dataset.liked === '1'){
            btn.dataset.liked = '';
            btn.style.background = '';
            btn.style.color = '';
          } else {
            btn.dataset.liked = '1';
            btn.style.background = 'linear-gradient(180deg,#ffdede,#ffecec)';
            btn.style.color = '#a91b1b';
          }
        }
        
        if(btn.textContent.trim() === 'Cevapla'){
          // Cevapla işlevi
          const item = btn.closest('.yorumkutusu-item');
          const name = item.querySelector('.yorumkutusu-item-name').textContent || 'Kullanıcı';
          textInput.value = `@${name} `;
          textInput.focus();
        }
      });

             // Rating yıldızları için hover efekti
       const ratingStars = document.querySelectorAll('.rating-stars .star-label');
       ratingStars.forEach((star, index) => {
         star.addEventListener('mouseenter', () => {
           // Hover edilen yıldıza kadar olan tüm yıldızları sarı yap
           ratingStars.forEach((s, i) => {
             if (i <= index) {
               s.style.color = '#fbbf24';
             } else {
               s.style.color = '#d1d5db';
             }
           });
         });
       });

       // Rating container'dan çıkınca orijinal duruma dön
       const ratingContainer = document.querySelector('.rating-stars');
       ratingContainer.addEventListener('mouseleave', () => {
         const checkedStar = document.querySelector('input[name="rating"]:checked');
         const checkedValue = checkedStar ? parseInt(checkedStar.value) : 5;
         
         ratingStars.forEach((star, index) => {
           if (index < checkedValue) {
             star.style.color = '#fbbf24';
           } else {
             star.style.color = '#d1d5db';
           }
         });
       });

       // Sayfa yüklendiğinde sayacı güncelle
       updateCount();

     })();
  </script>

          </div>



        <div class="bilgiler-basliklar">
          <div class="bilgiler-basliklar-cizgi">
            <h1>Benzer Kitaplar</h1>
          </div>
        </div>

        <!-- Card Slider -->
        <div class="card-slider-container" id="similar-slider">
            <button class="card-slider-btn card-slider-btn-prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="card-slider-btn card-slider-btn-next">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="card-slider">
                <div class="card-slider-track">
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="124">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="124">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="125">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="125">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="126">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="126">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="127">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="127">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="128">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="128">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card">
                        <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                            <button class="see" type="submit" name="kitap-detay" value="129">
                                <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                        <form class="card-form" action="" method="POST">
                            <button class="kalp-button" type="submit" name="kitap-ID" value="129">
                                <div class="kalp"></div>
                            </button>
                            <button class="i">
                                <h2 translate="no">i</h2>
                            </button>
                            <button class="OKL-ekle">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--FOOTER-->
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

    <!-- Card Slider JavaScript -->
    <script>
class CardSlider {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.track = this.container?.querySelector('.card-slider-track');
    this.cards = this.container?.querySelectorAll('.card-slider-track .card') || [];

    if (!this.container || !this.track || !this.cards.length) return;

    this.cardWidth = 297;              // kart genişliği + gap
    this.animationInterval = 3000;     // her adım arası bekleme
    this.stepDuration = 500;           // tek adım animasyon süresi (ms) - transition ile uyumlu
    this.isPaused = false;
    this.isAnimating = false;
    this.timer = null;

    this.init();
  }

  init() {
    this.setupAnimation();
    this.addHoverEffects();
    this.addTouchSupport();
    this.addButtonControls();
  }

  // 🔥 Kopya KART YOK. Sadece 6 orijinal kartı döndürüyoruz.
  setupAnimation() {
    this.track.style.willChange = 'transform';
    this.track.style.transition = 'none';
    this.track.style.transform = 'translateX(0)';
    // İlk adımı planla
    this.scheduleNext();
  }

  scheduleNext() {
    if (this.timer) clearTimeout(this.timer);
    if (!this.isPaused) {
      this.timer = setTimeout(() => this.stepForward(), this.animationInterval);
    }
  }

  // 🔥 Her adım: sola -cardWidth kaydır → animasyon bitince ilk kartı sona taşı → anında 0'a geri kur
  stepForward() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    // Animasyonu başlat
    requestAnimationFrame(() => {
      this.track.style.transition = `transform ${this.stepDuration}ms ease-in-out`;
      this.track.style.transform = `translateX(-${this.cardWidth}px)`;

      const onEnd = () => {
        this.track.removeEventListener('transitionend', onEnd);

        // İlk kartı sona taşı (KOPYA DEĞİL, TAŞIMA)
        this.track.appendChild(this.track.firstElementChild);

        // Anında 0 konumuna dön (göz kırpmadan)
        this.track.style.transition = 'none';
        this.track.style.transform = 'translateX(0)';

        // Reflow zorla
        void this.track.offsetWidth;

        this.isAnimating = false;
        this.scheduleNext();
      };

      this.track.addEventListener('transitionend', onEnd, { once: true });
    });
  }

  // Butonlar da aynı mantıkla çalışsın
  moveToNext() {
    if (this.isAnimating) return;
    this.isPaused = true;
    clearTimeout(this.timer);
    this.stepForward();
    // Adım bitince otomatik scheduleNext zaten çağrılıyor, tekrar başlatırız:
    this.isPaused = false;
  }

  moveToPrevious() {
    if (this.isAnimating) return;
    this.isPaused = true;
    clearTimeout(this.timer);

    // Son kartı başa al → -cardWidth pozisyonuna koy → 0'a doğru animasyon
    this.track.style.transition = 'none';
    this.track.insertBefore(this.track.lastElementChild, this.track.firstElementChild);
    this.track.style.transform = `translateX(-${this.cardWidth}px)`;
    void this.track.offsetWidth; // reflow

    this.isAnimating = true;
    this.track.style.transition = `transform ${this.stepDuration}ms ease-in-out`;
    this.track.style.transform = 'translateX(0)';

    this.track.addEventListener('transitionend', () => {
      this.isAnimating = false;
      this.isPaused = false;
      this.scheduleNext();
    }, { once: true });
  }

  addHoverEffects() {
    this.cards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'scale(1.02) translateY(-5px)';
        card.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.4)';
        card.style.zIndex = '100';
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = 'scale(0.95)';
        card.style.boxShadow = '0 0 30px 2px rgba(0, 0, 0, 0.3)';
        card.style.zIndex = '1';
      });
    });
  }

  addTouchSupport() {
    let startX = 0, isDragging = false;

    this.container.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      isDragging = true;
      this.isPaused = true;
      clearTimeout(this.timer);
    });

    this.container.addEventListener('touchmove', (e) => {
      if (!isDragging) return;
      const diff = e.touches[0].clientX - startX;
      // İstersen diff ile anlık sürükleme de yapabilirsin (gerekirse ekleriz)
    });

    this.container.addEventListener('touchend', () => {
      isDragging = false;
      this.isPaused = false;
      this.scheduleNext();
    });
  }

  addButtonControls() {
    const prevBtn = this.container.querySelector('.card-slider-btn-prev');
    const nextBtn = this.container.querySelector('.card-slider-btn-next');

    prevBtn?.addEventListener('click', () => this.moveToPrevious());
    nextBtn?.addEventListener('click', () => this.moveToNext());
  }
}

// DOM yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => {
  new CardSlider('slider1');
  new CardSlider('slider2');
  new CardSlider('slider3');
});
</script>











<script>
document.addEventListener('DOMContentLoaded', () => {
  const reserveForm = document.getElementById('rezerve-form');
  const bildirim = document.querySelector('.bilgiler-bildirim2');
  if (!reserveForm || !bildirim) return;

  // Başlangıç erişilebilirlik durumu
  bildirim.setAttribute('aria-hidden', 'true');

  let sending = false;

  reserveForm.addEventListener('submit', async function (e) {
    e.preventDefault();            // sayfa yenilenmesin
    if (sending) return;           // tekrar gönderimi engelle
    sending = true;

    const submitBtn = reserveForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.setAttribute('aria-busy', 'true');
    }

    try {
      // FormData topla (session cookie'leri gönderilsin diye credentials: 'same-origin')
      const fd = new FormData(reserveForm);

      const resp = await fetch(reserveForm.action, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
        redirect: 'follow'
      });

      // Bildirimi göstermek için sınıf ekle
      bildirim.classList.add('bildirims');
      bildirim.setAttribute('aria-hidden', 'false');

      // 2 saniye sonra sınıfı kaldır (1s istersen 1000 yap)
      setTimeout(() => {
        bildirim.classList.remove('bildirims');
        bildirim.setAttribute('aria-hidden', 'true');
      }, 2000);

      // Başarılı ise butonu kalıcı gri/disabled bırak
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.backgroundColor = 'gray';
        submitBtn.style.pointerEvents = 'none';
        submitBtn.removeAttribute('aria-busy');
      }

    } catch (err) {
      console.error('Ödünç alma hatası:', err);
      // hata olsa bile kısa gösterip gizle
      bildirim.classList.add('bildirims');
      bildirim.setAttribute('aria-hidden', 'false');
      setTimeout(() => {
        bildirim.classList.remove('bildirims');
        bildirim.setAttribute('aria-hidden', 'true');
      }, 2000);
    } finally {
      sending = false;
      // Başarı durumunda disabled bırakıldı; burada tekrar aktif etmiyoruz
    }
  });

  // Rezervasyon state kontrolü (sayfa yenilemeden): finish olunca butonu aç
  (function pollReservation(){
    const btn = document.querySelector('#rezerve-form button[type="submit"]');
    if (!btn) return;
    const userId = <?php echo (int)$bmUserId; ?>;
    const bookId = <?php echo (int)($bookId ?? 0); ?>;
    if (!userId || !bookId) return;
    async function check(){
      try {
        const res = await fetch(`reservation_status.php?user_id=${encodeURIComponent(userId)}&book_id=${encodeURIComponent(bookId)}`, {credentials:'same-origin'});
        const data = await res.json();
        const st = (data && typeof data.state === 'string') ? data.state.toLowerCase().trim() : '';
        if (st === 'finish') {
          btn.disabled = false;
          btn.style.backgroundColor = '';
          btn.style.pointerEvents = '';
          // Session kilidini de kaldır
          try { await fetch(`clear_reserve_lock.php?book_id=${encodeURIComponent(bookId)}`, {credentials:'same-origin'}); } catch(e) {}
          return; // durdur
        }
      } catch(e) {}
      setTimeout(check, 5000);
    }
    check();
  })();

});
</script>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const oduncForm = document.getElementById('odunc-form');
  const bildirim = document.querySelector('.bilgiler-bildirim');
  if (!oduncForm || !bildirim) return;

  // Başlangıç erişilebilirlik durumu
  bildirim.setAttribute('aria-hidden', 'true');

  let sending = false;

  oduncForm.addEventListener('submit', async function (e) {
    e.preventDefault();            // sayfa yenilenmesin

    const submitBtn = oduncForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.setAttribute('aria-busy', 'true');
    }

    try {
      // FormData topla (session cookie'leri gönderilsin diye credentials: 'same-origin')
      const fd = new FormData(oduncForm);

      const resp = await fetch(oduncForm.action, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
        redirect: 'follow'
      });

      // Bildirimi göstermek için sınıf ekle
      bildirim.classList.add('bildirims');
      bildirim.setAttribute('aria-hidden', 'false');

      // 2 saniye sonra sınıfı kaldır (1s istersen 1000 yap)
      setTimeout(() => {
        bildirim.classList.remove('bildirims');
        bildirim.setAttribute('aria-hidden', 'true');
      }, 2000);

      // Başarılı ise butonu kalıcı gri/disabled bırak
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.backgroundColor = 'gray';
        submitBtn.style.pointerEvents = 'none';
        submitBtn.removeAttribute('aria-busy');
      }

    } catch (err) {
      console.error('Ödünç alma hatası:', err);
      // hata olsa bile kısa gösterip gizle
      bildirim.classList.add('bildirims');
      bildirim.setAttribute('aria-hidden', 'false');
      setTimeout(() => {
        bildirim.classList.remove('bildirims');
        bildirim.setAttribute('aria-hidden', 'true');
      }, 2000);
    } finally {
      sending = false;
      // Başarı durumunda disabled bırakıldı; burada tekrar aktif etmiyoruz
    }
  });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('favori-btn');
  if (!btn) return;

  let isFavorite = <?php echo (int)$initialIsFavorite; ?> === 1;

  function render() {
    if (isFavorite) {
      btn.textContent = '❤️Favori';
      btn.setAttribute('aria-pressed', 'true');
      // burada isFavorite ise butonu pasifleştirecek misin karar ver
      btn.disabled = false; // öneri: kullanıcı favoriyi kaldırabilsin
    } else {
      btn.textContent = 'Favori';
      btn.setAttribute('aria-pressed', 'false');
      btn.disabled = false;
    }
  }

  render();

  let sending = false;
  btn.addEventListener('click', async () => {
    if (sending) return;
    const userId = <?php echo (int)$bmUserId; ?>;
    const bookId = <?php echo (int)($bookId ?? 0); ?>;
    if (!userId || !bookId) {
      console.warn('userId veya bookId eksik', userId, bookId);
      return;
    }

    sending = true;
    btn.disabled = true;

    const desired = isFavorite ? '0' : '1';
    const body = new URLSearchParams();
    body.set('book_id', String(bookId));
    body.set('is_favorite', desired);

    try {
      const resp = await fetch('toggle_favorite.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      });

      const raw = await resp.text();              // önce ham metni al
      console.log('toggle_favorite raw response:', resp.status, raw);

      if (!resp.ok) {
        // HTTP düzeyinde hata
        alert('Sunucu hatası (HTTP ' + resp.status + '). Konsolu kontrol edin.');
        console.error('HTTP error:', resp.status, raw);
        return;
      }

      // JSON parse denemesi
      let data = null;
      try {
        data = JSON.parse(raw);
      } catch (err) {
        console.error('JSON parse hatası, sunucudan gelen ham yanıt:', raw);
        alert('Sunucudan beklenmeyen yanıt alındı. Lütfen geliştirici konsolunu kontrol edin.');
        return;
      }

      // Beklenen formatı doğrula
      if (data && (data.ok === 1 || data.ok === '1' || data.ok === true)) {
        // sunucunun döndürdüğü isFavorite alanını kullan (örn "isFavorite": "1")
        isFavorite = (data.isFavorite === '1' || data.isFavorite === 1 || data.isFavorite === true);
        render();
      } else {
        console.error('Favori yanıtı beklenmeyen formatta:', data);
        alert('Favori işlemi başarısız oldu. Konsolu kontrol edin.');
      }
    } catch (e) {
      console.error('Favori işlem hatası:', e);
      alert('Ağ hatası oluştu. Lütfen tekrar deneyin.');
    } finally {
      sending = false;
      btn.disabled = false;
    }
  });
});
</script>


</body>
</html>