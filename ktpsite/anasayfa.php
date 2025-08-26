<?php
session_start();
require_once 'repositories/database_repository.php';

// Eğer giriş kontrolü istersen buraya ekle:
// if (!isset($_SESSION['email'])) { header('Location: giris.php'); exit; }

/**
 * ensureMinItems:
 * - Eğer gelen dizi 4'ten az ise mevcut kayıtları döndürerek 4'e tamamlar.
 * - Eğer hiç kayıt yoksa placeholder bir öğe ekler.
 */
function ensureMinItems(array $items, int $min = 4) {
    if (count($items) >= $min) return $items;
    $i = 0;
    while (count($items) < $min) {
        if (count($items) === 0) {
            $items[] = [
                'BOOK_ID' => 0,
                'BOOK_NAME' => 'Yakında eklenecek',
                'BOOK_IMAGE' => '',
                'BOOK_SUMMARY' => 'Bilgi yakında eklenecek.'
            ];
        } else {
            $items[] = $items[$i % count($items)];
            $i++;
        }
    }
    return $items;
}

// Verileri al
$featured = getFeaturedBooks(8);   // Öne çıkanlar — borrow_records RETURN_DATE'e göre
$newArrivals = getNewArrivals(8);  // Yeni gelenler — books ADDED_DATE'e göre

// En az 4 sağla
$featured = ensureMinItems($featured, 4);
$newArrivals = ensureMinItems($newArrivals, 4);

// Placeholder resim yolu (kendi path'ine göre düzenle)
$placeholderImage = 'images/placeholder.png';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F8 Kütüphane - Ana Sayfa</title>
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
  margin-top: 140px;
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

/* Kategori Çubuğu Stilleri */
.category-scroll-wrapper * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.category-scroll-wrapper {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    padding: 40px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 30px;
}

.category-container {
    width: 1000px;
    height: 40px;
    background: #f8f8f8;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.06), inset 0 -1px 3px rgba(0,0,0,0.06);
}

.category-scroll {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 30px;
    gap: 25px;
    cursor: grab;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-behavior: smooth;
    width: 100%;
}

.category-scroll::-webkit-scrollbar {
    display: none;
}

.category-scroll:active {
    cursor: grabbing;
}

.category-item {
    padding: 8px 20px;
    font-weight: 600;
    font-size: 16px;
    font-family: cursive;
    color: #555;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    user-select: none;
    border: 2px solid transparent;
    position: relative;
    z-index: 1;
    background: transparent;
    flex-shrink: 0;
}

.category-item.empty {
    opacity: 0;
    pointer-events: none;
    padding: 8px 20px;
    width: 0;
    min-width: 0;
}

.category-item:hover {
    transform: translateY(-2px);
    color: #333;
}

.category-item.active {
    background: linear-gradient(135deg, #f0f0f0, #e5e5e5);
    color: #444;
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    border: 2px solid #e0e0e0;
}

.category-item.fade-left {
    opacity: 0.15;
    transform: scale(0.85);
    filter: blur(0.5px);
}

.category-item.fade-right {
    opacity: 0.15;
    transform: scale(0.85);
    filter: blur(0.5px);
}

.shadow-left, .shadow-right {
    position: absolute;
    top: 0;
    width: 150px;
    height: 100%;
    pointer-events: none;
    z-index: 2;
}

.shadow-left {
    left: 0;
    background: linear-gradient(to right, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 50%, rgba(255, 255, 255, 0) 100%);
}

.shadow-right {
    right: 0;
    background: linear-gradient(to left, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 50%, rgba(255, 255, 255, 0) 100%);
}

.keyboard-hint {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    color: #999;
    font-family: cursive;
}

</style>
<?php
require_once 'repositories/database_repository.php';

$repo = new OracleCategoryRepository();
$categories = $repo->getAll();
?>






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
        <a class="navbar-cikis-link" onclick="return confirm('Çıkış yapmak istiyor musunuz?')" href="cikis.php"><i style="color: white; margin-left: 30px; margin-right: 20px;" class="fas fa-sign-out-alt fa-3x"></i></a>
    </nav>

    <!--NAVBAR-->
    <!--SLİDER-->

    <div class="slider-background">
        <button class="slider-nav-btn-prev" onclick="plusSlides(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
         <div class="slider">
        <div class="slide active">
            <img class="ktp-imgs" src="images/ktp.jpg" alt="ktp" />
            <div class="slide-caption">
                <h2>Her Kitleye Uygun Kitaplar</h2>
                <p>Her kitleye uygun kitapları burada bulabilirsiniz</p>
            </div>
        </div>
        <div class="slide">
            <img class="ktp-imgs" src="images/ktp2.jpg" alt="ktp2" />
            <div class="slide-caption">
                <h2>Yeni Gelen Kitaplar Raflarda!</h2>
                <p>Yeni gelenler kategorisinden erişim sağlayabilirsiniz</p>
            </div>
        </div>
        <div class="slide">
            <img class="ktp-imgs" src="images/ktp3.jpg" alt="ktp3" />
            <div class="slide-caption">
                <h2>Kolay Arama, Hızlı Erişim</h2>
                <p>Birçok farklı seçenekle arama yapabilirsiniz </p>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        
        
        <!-- Indicators -->
        <div class="slider-indicators">
            <span class="slider-indicator active" onclick="currentSlide(1)"></span>
            <span class="slider-indicator" onclick="currentSlide(2)"></span>
            <span class="slider-indicator" onclick="currentSlide(3)"></span>
        </div>
      </div>
        <button class="slider-nav-btn-next" onclick="plusSlides(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
        
    <!--SLİDER-->
    <!-- KATEGORİLER ÇUBUĞU -->
<div class="category-scroll-wrapper">
  <div class="category-container">
    <div class="keyboard-hint"></div>
    <div class="shadow-left"></div>
    <div class="shadow-right"></div>

    <div class="category-scroll" id="categoryScroll">
      <!-- Baştaki boş item’lar -->
      <?php for ($i = 1; $i <= 6; $i++): ?>
        <div class="category-item empty" data-category="empty<?= $i ?>"></div>
      <?php endfor; ?>

      <!-- "Tümü" seçeneği -->
      <div class="category-item" data-id="all">Tümü</div>

      <!-- Dinamik kategoriler -->
      <?php foreach ($categories as $cat): ?>
        <div class="category-item"
              data-id="<?= htmlspecialchars($cat['CATEGORY_ID']) ?>">
              <?= htmlspecialchars($cat['DESCRIPTION']) ?>
        </div>
      <?php endforeach; ?>

      <!-- Sondaki boş item’lar -->
      <?php for ($i = 7; $i <= 12; $i++): ?>
        <div class="category-item empty" data-category="empty<?= $i ?>"></div>
      <?php endfor; ?>
    </div>
  </div>
</div>
<!-- KATEGORİLER ÇUBUĞU -->

    <!--ARAMA ÇUBUĞU-->

    

    <form class="search-form" method="GET" action="search.php">
          <input class="search-input" type="text" name="arama" placeholder="Kitap, yazar veya yayınevi yazın">
          <i class="search-icon fa-solid fa-search fa-1.5x"></i>
          <div id="search-results" class="search-results"></div>
    </form>

    <div class="spinner-div" id="spinner-div">
      <div class="spinner"></div>
    </div>

   <!--ARAMA ÇUBUĞU-->
   


    
    <h2 id="kategori-baslik" style="display: block; font-size: 24px; color: rgb(51, 51, 51); width: 1100px; position: relative; left: 50%; transform: translateX(-50%); justify-content: flex-start; display: flex; font-family: system-ui;"></h2>
    <div class="card-wrapper hidden" id="cardWrapper">
       
    </div>


    


    <div id="cards-container" style=" width: 1155px; height: auto; display: flex; gap: 85px; position: relative; left: 50%; transform: translateX(-50%); flex-wrap: wrap;"></div>
  
    <div id="default-cards"  class="card-container" >
    

       


        <div class="anasayfa-basliklar">
      <div class="anasayfa-basliklar-cizgi">
        <h1>Öne Çıkanlar</h1>
      </div>
    </div>

    <div class="card-slider-container" id="slider1">
        <button class="card-slider-btn card-slider-btn-prev" data-slider="slider1"><i class="fas fa-chevron-left"></i></button>
        <button class="card-slider-btn card-slider-btn-next" data-slider="slider1"><i class="fas fa-chevron-right"></i></button>
        <div class="card-slider">
            <div class="card-slider-track">
                <?php foreach ($featured as $book): 
                    $bookId = htmlspecialchars($book['BOOK_ID'] ?? 0, ENT_QUOTES, 'UTF-8');
                    $description = htmlspecialchars($book['DESCRIPTION'] ?? '', ENT_QUOTES, 'UTF-8');
                    $bookName = htmlspecialchars($book['BOOK_NAME'] ?? 'Başlıksız', ENT_QUOTES, 'UTF-8');
                    $bookImage = trim($book['BOOK_IMAGE'] ?? '');
                    if ($bookImage === '') $bookImage = $placeholderImage;
                    $bookImage = htmlspecialchars($bookImage, ENT_QUOTES, 'UTF-8');
                    $bookSummary = htmlspecialchars($book['BOOK_SUMMARY'] ?? '', ENT_QUOTES, 'UTF-8');
                ?>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin:0;padding:0;">
                        <button class="see" type="submit" name="kitap-detay" value="<?php echo $bookId; ?>" style="background:none;border:none;padding:0;cursor:pointer;">
                            <img src="<?php echo $bookImage; ?>" alt="<?php echo $bookName; ?>">
                        </button>
                    </form>
                    <h2 class="card-h2"><?php echo $bookName; ?></h2>
                    <form class="card-form" action="" method="POST" onsubmit="return false;">
                        <button class="i" type="button">
                            <h2 translate="no">i</h2>
                            <div class="tooltip"><?php echo $description; ?></div>
                        </button>
                        <button class="kalp-button" type="button" data-book-id="<?php echo $bookId; ?>">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button" data-book-id="<?php echo $bookId; ?>">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!--HAREKETLİ CARD SLİDER-->

    <div class="anasayfa-basliklar">
      <div class="anasayfa-basliklar-cizgi">
        <h1>Yeni Gelenler</h1>
      </div>
    </div>

    <div class="card-slider-container" id="slider2">
        <button class="card-slider-btn card-slider-btn-prev" data-slider="slider2"><i class="fas fa-chevron-left"></i></button>
        <button class="card-slider-btn card-slider-btn-next" data-slider="slider2"><i class="fas fa-chevron-right"></i></button>
        <div class="card-slider">
            <div class="card-slider-track">
                <?php foreach ($newArrivals as $book): 
                    $bookId = htmlspecialchars($book['BOOK_ID'] ?? 0, ENT_QUOTES, 'UTF-8');
                    $bookName = htmlspecialchars($book['BOOK_NAME'] ?? 'Başlıksız', ENT_QUOTES, 'UTF-8');
                    $bookImage = trim($book['BOOK_IMAGE'] ?? '');
                    if ($bookImage === '') $bookImage = $placeholderImage;
                    $bookImage = htmlspecialchars($bookImage, ENT_QUOTES, 'UTF-8');
                    $bookSummary = htmlspecialchars($book['BOOK_SUMMARY'] ?? '', ENT_QUOTES, 'UTF-8');
                ?>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin:0;padding:0;">
                        <button class="see" type="submit" name="kitap-detay" value="<?php echo $bookId; ?>" style="background:none;border:none;padding:0;cursor:pointer;">
                            <img src="<?php echo $bookImage; ?>" alt="<?php echo $bookName; ?>">
                        </button>
                    </form>
                    <h2 class="card-h2"><?php echo $bookName; ?></h2>
                    <form class="card-form" action="" method="POST" onsubmit="return false;">
                        <button class="i" type="button">
                            <h2 translate="no">i</h2>
                            <div class="tooltip"><?php echo $bookSummary; ?></div>
                        </button>
                        <button class="kalp-button" type="button" data-book-id="<?php echo $bookId; ?>">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button" data-book-id="<?php echo $bookId; ?>">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>




    <div class="anasayfa-basliklar">
        <div class="anasayfa-basliklar-cizgi">
            <h1>Sizin İçin Seçtiklerimiz</h1>
        </div>
    </div>


    <div class="card-slider-container" id="slider3">
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
                            <button class="see" type="submit" name="kitap-detay" value="123">
                               <img src="images/george_orwell_1984.png">
                            </button>
                        </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                        <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                        <button class="see" type="submit" name="kitap-detay" value="124">
                            <img src="images/george_orwell_1984.png">
                        </button>
                    </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                        <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                        <button class="see" type="submit" name="kitap-detay" value="125">
                            <img src="images/george_orwell_1984.png">
                        </button>
                    </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                        <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                        <button class="see" type="submit" name="kitap-detay" value="126">
                            <img src="images/george_orwell_1984.png">
                        </button>
                    </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                        <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                        <button class="see" type="submit" name="kitap-detay" value="127">
                            <img src="images/george_orwell_1984.png">
                        </button>
                    </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                       <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
                <div class="card">
                    <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                        <button class="see" type="submit" name="kitap-detay" value="128">
                            <img src="images/george_orwell_1984.png">
                        </button>
                    </form>
                    <h2 class="card-h2">George Orwell 1984</h2>
                    <form class="card-form" action="" method="POST">
                       <button class="i">
                            <h2 translate="no">i</h2>
                            <div class="tooltip">Bilim Kurgu</div>
                        </button>
                        <button class="kalp-button" type="button" name="kitap-ID" value="123">
                            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                        </button>
                        <button class="OKL-ekle" type="button">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    </div>


    <div id="category-cards" class="content-block">
        <!-- PHP ile seçilen kategori için buraya card’leri echo edeceğiz -->
    </div>


    <div id="content-tumu" class="content-block">
    <!-- “Tümü” için göstermek istediğin card’ler buraya -->
    </div>

    <div id="content-roman" class="content-block">
    <!-- “Roman” seçilince gösterilecek card’ler buraya -->
    </div>

    <div id="content-edebiyat" class="content-block">
    
    </div>

    <div id="content-hikaye" class="content-block">
    
    </div>

    <div id="content-felsefe" class="content-block">
    
    </div>

    <div id="content-tarih" class="content-block">
    
    </div>

    <div id="content-sosyoloji" class="content-block">
    
    </div>

    <div id="content-psikoloji" class="content-block">
    
    </div>

    <div id="content-ekonomiİsletme" class="content-block">
    
    </div>

    <div id="content-sanat" class="content-block">
    
    </div>

    <div id="content-din" class="content-block">
    
    </div>

    <div id="content-toplumSiyaset" class="content-block">
    
    </div>

    <div id="content-saglik" class="content-block">
    
    </div>

    <div id="content-cocuk" class="content-block">
    
    </div>

    <div id="content-dil" class="content-block">
    
    </div>

    <div id="content-muzik" class="content-block">
    
    </div>

    <div id="content-kisiselGelisim" class="content-block">
    
    </div>

    <div id="content-siir" class="content-block">
    
    </div>

    <div id="content-kultur" class="content-block">
    
    </div>

    <div id="content-mimari" class="content-block">
    
    </div>

    <div id="content-mimari" class="content-block">
    
    </div>

    <div id="content-yazilimProgramlama" class="content-block">
    
    </div>

    <div id="content-isletmeYonetim" class="content-block">
    
    </div>

    <div id="content-fotografcilik" class="content-block">
    
    </div>

    <div id="content-arkeoloji" class="content-block">
    
    </div>

    <div id="content-siyaset" class="content-block">
    
    </div>

    <div id="content-gezi" class="content-block">
    
    </div>

    <div id="content-deneme" class="content-block">
    
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

    <!--FOOTER-->
    


    <script>
    let slideIndex = 0;
    let slideInterval;
    const slides = document.getElementsByClassName("slide");
    const indicators = document.getElementsByClassName("slider-indicator");
    
    // Initialize slider
    showSlides(slideIndex);
    startAutoSlide();
    
    function plusSlides(n) {
        showSlides(slideIndex += n);
        resetAutoSlide();
    }
    
    function currentSlide(n) {
        showSlides(slideIndex = n - 1);
        resetAutoSlide();
    }
    
    function showSlides(n) {
        // Handle slide index bounds
        if (n >= slides.length) slideIndex = 0;
        if (n < 0) slideIndex = slides.length - 1;
        
        // Remove active class from all slides and indicators
        for (let i = 0; i < slides.length; i++) {
            slides[i].classList.remove('active');
            if (indicators[i]) {
                indicators[i].classList.remove('active');
            }
        }
        
        // Add active class to current slide and indicator
        slides[slideIndex].classList.add('active');
        if (indicators[slideIndex]) {
            indicators[slideIndex].classList.add('active');
        }
    }
    
    function startAutoSlide() {
        slideInterval = setInterval(() => {
            plusSlides(1);
        }, 3000);
    }
    
    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }
    
    // Pause auto-slide on hover
    const slider = document.querySelector('.slider');
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
        startAutoSlide();
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            plusSlides(-1);
        } else if (e.key === 'ArrowRight') {
            plusSlides(1);
        }
    });
    </script>

    <!-- Kategori Çubuğu JavaScript -->
    <script>
document.addEventListener('DOMContentLoaded', () => {

  // Yardımcı: cardWrapper'ı göstermek
  function showCardWrapper(el) {
    if (!el) return;
    el.style.display = 'flex';
    setTimeout(() => {
      el.style.opacity = '1';
    }, 50); // küçük gecikme ile yumuşak geçiş
  }

  // Yardımcı: cardWrapper'ı gizlemek
  function hideCardWrapper(el) {
    if (!el) return;
    el.style.opacity = '0';
    setTimeout(() => {
      el.style.display = 'none';
      el.innerHTML = '';
    }, 400); // opacity animasyonuna göre
  }

  class CategoryScroll {
    constructor() {
      this.container = document.querySelector('.category-container');
      this.scroll = document.querySelector('.category-scroll');
      this.items = document.querySelectorAll('.category-item');
      this.isDragging = false;
      this.startX = 0;
      this.scrollLeft = 0;
      this.currentCategory = null;
      this.cardWrapper = document.getElementById('cardWrapper');
      this.cardsContainer = document.getElementById('cards-container');
      this.spinnerEl = document.getElementById('spinner-div');
      this.init();
    }

    init() {
      this.setupEventListeners();
      this.updateActiveItem();
      this.updateFadeEffects();
      hideCardWrapper(this.cardWrapper); // Sayfa açılınca gizli
    }

    setupEventListeners() {
      this.items.forEach(item => {
        if (!item.classList.contains('empty')) {
          item.addEventListener('click', () => this.activateCategory(item));
        }
      });

      // Mouse & touch drag
      this.scroll.addEventListener('mousedown', this.startDragging.bind(this));
      document.addEventListener('mousemove', this.drag.bind(this));
      document.addEventListener('mouseup', this.stopDragging.bind(this));
      this.scroll.addEventListener('touchstart', this.startDragging.bind(this), {passive:false});
      document.addEventListener('touchmove', this.drag.bind(this), {passive:false});
      document.addEventListener('touchend', this.stopDragging.bind(this));

      // Wheel
      this.container.addEventListener('wheel', this.handleWheel.bind(this), {passive:false});

      // Scroll efektleri
      this.scroll.addEventListener('scroll', () => {
        this.updateActiveItem();
        this.updateFadeEffects();
      });

      // Keyboard
      document.addEventListener('keydown', this.handleKeyboard.bind(this));
    }

    startDragging(e) {
      this.isDragging = true;
      this.startX = e.type === 'mousedown' ? e.pageX : e.touches[0].pageX;
      this.scrollLeft = this.scroll.scrollLeft;
      this.scroll.style.cursor = 'grabbing';
      e.preventDefault();
    }

    drag(e) {
      if (!this.isDragging) return;
      e.preventDefault();
      const x = e.type === 'mousemove' ? e.pageX : e.touches[0].pageX;
      const walk = (this.startX - x) * 1.2;
      this.scroll.scrollLeft = this.scrollLeft + walk;
    }

    stopDragging() {
      this.isDragging = false;
      this.scroll.style.cursor = 'grab';
    }

    async activateCategory(item) {
      
      
      const categoryId = item.dataset.id;
      if (!categoryId) return;

      // Aynı kategoriye tıklanırsa gizle
      if (this.currentCategory === categoryId) {
        this.currentCategory = null;
        hideCardWrapper(this.cardWrapper);
        item.classList.remove('active');
        // Kapatınca ana kartları geri göster
        if (this.cardsContainer) this.cardsContainer.style.display = '';
        // Spinner'ı kapat
        if (this.spinnerEl) this.spinnerEl.style.display = 'none';
        return;
      }

      // Yeni kategori seçildi
      this.currentCategory = categoryId;
      this.items.forEach(i => i.classList.remove('active'));
      item.classList.add('active');

      // Ön hazırlık: ana kartları gizle, mevcut kategori kartlarını gizle, spinner'ı göster
      if (this.cardsContainer) this.cardsContainer.style.display = 'none';
      hideCardWrapper(this.cardWrapper);
      this.cardWrapper.innerHTML = '';
      if (this.spinnerEl) this.spinnerEl.style.display = 'flex';
      await this.fetchCategoryBooks(categoryId);

      // Veri geldikten sonra: spinner'ı gizle ve wrapper'ı göster
      if (this.spinnerEl) this.spinnerEl.style.display = 'none';
      showCardWrapper(this.cardWrapper);
    }

    async fetchCategoryBooks(categoryId) {
      try {
        const url = (categoryId === 'all')
          ? `kitaplari_getir.php?kategori=all`
          : `kitaplari_getir.php?kategori=${encodeURIComponent(categoryId)}`;
        const res = await fetch(url);
        const data = await res.json();

        if (!Array.isArray(data)) {
          console.error('Sunucu hatası:', data.error || data);
          return;
        }

        data.forEach(b => {
          const img = b.BOOK_IMAGE || '';
          const name = b.BOOK_NAME || '';
          const id = b.BOOK_ID || '';
          const categoryText = b.CATEGORY_NAME || b.DESCRIPTION || '';

          const card = document.createElement('div');
          card.className = 'card9';
          card.style.opacity = '0';
          card.style.transition = 'opacity 0.4s';
          card.innerHTML = `
            <form action="bilgiler.php" method="POST" style="margin:0;padding:0;">
              <button class="see9" type="submit" name="kitap-detay" value="${id}">
                <img src="${img}" alt="${name}">
              </button>
            </form>
            <h2 class="card-h9">${name}</h2>
            <form class="card-form9" action="" method="POST">
              <button class="i" type="button">
                <h2 translate="no">i</h2>
                <div class="tooltip">${categoryText}</div>
              </button>
              <button class="kalp-button" type="button" name="kitap-ID" value="${id}">
                <i class="fa-regular fa-heart" style="font-size:20px;margin-top:3px;"></i>
              </button>
              <button class="OKL-ekle" type="button"><i class="fa-solid fa-check"></i></button>
            </form>
          `;
          this.cardWrapper.appendChild(card);

          // Hafif fade-in animasyonu
          setTimeout(() => {
            card.style.opacity = '1';
          }, 50);
        });

      } catch(err) {
        console.error('fetchCategoryBooks hata:', err);
      }
    }

    handleWheel(e) {
      e.preventDefault();
      this.scroll.scrollLeft += e.deltaY * 0.3;
    }

    handleKeyboard(e) {
      const activeItem = document.querySelector('.category-item.active');
      if (!activeItem) return;
      switch(e.key){
        case 'ArrowLeft': e.preventDefault(); this.scrollToPrevious(); break;
        case 'ArrowRight': e.preventDefault(); this.scrollToNext(); break;
        case 'Home': e.preventDefault(); this.scrollToFirst(); break;
        case 'End': e.preventDefault(); this.scrollToLast(); break;
      }
    }

    scrollToPrevious() {
      const activeItem = document.querySelector('.category-item.active');
      const prevItem = activeItem && activeItem.previousElementSibling;
      if (prevItem && prevItem.classList.contains('category-item')) prevItem.click();
    }

    scrollToNext() {
      const activeItem = document.querySelector('.category-item.active');
      const nextItem = activeItem && activeItem.nextElementSibling;
      if (nextItem && nextItem.classList.contains('category-item')) nextItem.click();
    }

    scrollToFirst() { if (this.items[0]) this.items[0].click(); }
    scrollToLast()  { if (this.items[this.items.length - 1]) this.items[this.items.length - 1].click(); }

    updateActiveItem() {
      const containerCenter = this.container.offsetWidth / 2;
      this.items.forEach(item => {
        if (!item.classList.contains('empty')) {
          const itemRect = item.getBoundingClientRect();
          const containerRect = this.container.getBoundingClientRect();
          const itemCenter = itemRect.left + itemRect.width/2;
          const distance = Math.abs(itemCenter - (containerRect.left + containerCenter));
          item.classList.remove('active');
          if (distance < 60) item.classList.add('active');
        }
      });
    }

    updateFadeEffects() {
      const containerRect = this.container.getBoundingClientRect();
      const leftEdge = containerRect.left + 80;
      const rightEdge = containerRect.right - 80;
      this.items.forEach(item => {
        if (!item.classList.contains('empty')) {
          const itemRect = item.getBoundingClientRect();
          item.classList.remove('fade-left','fade-right');
          if (itemRect.right < leftEdge) item.classList.add('fade-left');
          else if (itemRect.left > rightEdge) item.classList.add('fade-right');
        }
      });
    }
  }

  // Başlat
  new CategoryScroll();

});
</script>
<!--Kategoriler çubuğu javascript-->

<script>
// Favori ikonlarini baslatan yardimci
async function initFavoriteButtons(root){
  try {
    const scope = root || document;
    const btns = scope.querySelectorAll('.kalp-button');
    for (const btn of btns) {
      const bookId = btn.dataset.bookId || btn.getAttribute('value');
      if (!bookId) continue;
      try {
        const res = await fetch(`favorite_status.php?book_id=${encodeURIComponent(bookId)}`, {credentials:'same-origin'});
        const data = await res.json();
        const icon = btn.querySelector('i');
        const isFav = data && String(data.isFavorite) === '1';
        btn.classList.toggle('active', !!isFav);
        if (icon) {
          if (isFav) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
          else { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
        }
      } catch(_) {}
    }
  } catch(_) {}
}

document.addEventListener('DOMContentLoaded', () => { initFavoriteButtons(document); });

// Dinamik kart butonları için tekil, yetkili tıklama yakalayıcı
document.addEventListener('click', async (e) => {
  // Kalp (favori) butonu
  const favBtn = e.target.closest('.kalp-button');
  if (favBtn) {
    e.preventDefault();
    const bookId = favBtn.dataset.bookId || favBtn.getAttribute('value');
    try {
      if (bookId) {
        const desired = favBtn.classList.contains('active') ? '0' : '1';
        const body = new URLSearchParams();
        body.set('book_id', String(bookId));
        body.set('is_favorite', desired);
        const resp = await fetch('toggle_favorite.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString()
        });
        const data = await resp.json();
        const icon = favBtn.querySelector('i');
        const isFav = data && String(data.isFavorite) === '1';
        if (isFav) {
          favBtn.classList.add('active');
          if (icon) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
        } else {
          favBtn.classList.remove('active');
          if (icon) { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
        }
      } else {
        // bookId yoksa yalnızca görsel toggle yap
        favBtn.classList.toggle('active');
      }
    } catch (err) {
      console.error('Favori toggle hata:', err);
      favBtn.classList.toggle('active');
    }
    return;
  }

  // OKL-ekle butonu (yer işareti / sepet vb. görsel toggle)
  const addBtn = e.target.closest('.OKL-ekle');
  if (addBtn) {
    e.preventDefault();
    addBtn.classList.toggle('active');
    return;
  }
});
</script>

<script>
// Arama akışı: arama sırasında cardWrapper gizle, spinner göster; sonuçlar gelince spinner gizle
document.addEventListener('DOMContentLoaded', () => {
  const input = document.querySelector('.search-input');
  const results = document.getElementById('search-results');
  const spinner = document.getElementById('spinner-div');
  const cardWrapper = document.getElementById('cardWrapper');
  if (!input || !results) return;

  let debounceId = null;

  function renderResults(payload){
    if (!payload || typeof payload !== 'object') { results.style.display='none'; return; }
    const { kitaplar = [], yazarlar = [], yayinevleri = [] } = payload;
    let html = '';
    if (Array.isArray(kitaplar) && kitaplar.length) {
      html += '<div class="result-title">Kitaplar</div><ul>'; 
      kitaplar.slice(0,10).forEach(k => {
        const id = k.BOOK_ID ?? '';
        const name = (k.BOOK_NAME ?? '').toString();
        html += `<li data-id="${id}" data-type="book">${name}</li>`;
      });
      html += '</ul>';
    }
    if (Array.isArray(yazarlar) && yazarlar.length) {
      html += '<div class="result-title">Yazarlar</div><ul>';
      yazarlar.slice(0,10).forEach(y => {
        const id = y.AUTHOR_ID ?? '';
        const name = (y.AUTHOR_NAME ?? '').toString();
        html += `<li data-id="${id}" data-type="author">${name}</li>`;
      });
      html += '</ul>';
    }
    if (Array.isArray(yayinevleri) && yayinevleri.length) {
      html += '<div class="result-title">Yayınevleri</div><ul>';
      yayinevleri.slice(0,10).forEach(p => {
        const name = (p.PUBLISHER ?? '').toString();
        html += `<li data-id="${name}" data-type="publisher">${name}</li>`;
      });
      html += '</ul>';
    }
    results.innerHTML = html || '';
    results.style.display = html ? 'block' : 'none';
  }

  input.addEventListener('input', () => {
    const q = input.value.trim();
    if (debounceId) clearTimeout(debounceId);

    if (q === '') {
      // Temizlendi
      results.innerHTML = '';
      results.style.display = 'none';
      if (spinner) spinner.style.display = 'none';
      // Arama biterken cardWrapper tekrar görünebilir
      if (cardWrapper) { cardWrapper.style.display = 'flex'; cardWrapper.style.opacity = '1'; }
      return;
    }

    // Arama başladı → cardWrapper gizle, spinner göster
    if (cardWrapper) { cardWrapper.style.display = 'none'; }
    if (spinner) spinner.style.display = 'block';

    debounceId = setTimeout(async () => {
      try {
        const url = `search.php?type=all&term=${encodeURIComponent(q)}`;
        const resp = await fetch(url, { credentials: 'same-origin' });
        const data = await resp.json();
        renderResults(data);
      } catch (err) {
        console.error('search error', err);
        results.style.display = 'none';
      } finally {
        if (spinner) spinner.style.display = 'none';
      }
    }, 300); // küçük gecikme
  });

  // Sonuç tıklama: kitap ise detay sayfasına gönder
  results.addEventListener('click', (e) => {
    const li = e.target.closest('li');
    if (!li) return;
    const type = li.getAttribute('data-type');
    const id = li.getAttribute('data-id');
    if (type === 'book' && id) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'bilgiler.php';
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'kitap-detay';
      inp.value = id;
      form.appendChild(inp);
      document.body.appendChild(form);
      form.submit();
    }
  });
});
</script>









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
document.addEventListener('DOMContentLoaded', function () {
  const categoryItems = document.querySelectorAll('.category-item:not(.empty)');
  const cardContainer = document.querySelector('.card-wrapper');




  categoryItems.forEach(item => {
    item.addEventListener('click', function (event) {
      const categoryId = this.getAttribute('data-id');

      fetch('kitaplari_getir.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'categoryId=' + encodeURIComponent(categoryId)
      })
      .then(response => response.json())
      .then(data => {

           

        if (Array.isArray(data)) {
          cardContainer.innerHTML = ''; // Önceki kartları temizle
          data.forEach(book => {
            const img = book.BOOK_IMAGE || 'img/placeholder.png';
            const name = book.BOOK_NAME || '';
            const id = book.BOOK_ID;

            const cardHTML = `
              <div class="card9">
                <form action="bilgiler.php" method="POST" style="margin: 0; padding: 0;">
                  <button class="see9" type="submit" name="kitap-detay" value="${id}">
                    <img src="${img}" alt="Kitap Görseli">
                  </button>
                </form>
                <h2 class="card-h9">${name}</h2>
                <form class="card-form9" action="" method="POST">
                  <button class="i">
                    <h2 translate="no">i</h2>
                    <div class="tooltip">Bilim Kurgu</div>
                  </button>
                  <button class="kalp-button" type="button" name="kitap-ID" value="${id}">
                    <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
                  </button>
                  <button class="OKL-ekle" type="button">
                    <i class="fa-solid fa-check"></i>
                  </button>
                </form>
              </div>
            `;
            cardContainer.insertAdjacentHTML('beforeend', cardHTML);
          });
            const cardWrapper = document.getElementById('cardWrapper');
            cardWrapper.classList.remove('hidden');
            cardWrapper.classList.add('visible');

            // Başlığı oluştur
            const kategoriAdi = event.target.textContent;

            const kategoriBaslik = document.getElementById('kategori-baslik');
            kategoriBaslik.textContent = `"${kategoriAdi}" için sonuçlar gösteriliyor`;
            kategoriBaslik.style.display = 'block';
            kategoriBaslik.style.marginTop = '170px';


        } else {
          //cardContainer.innerHTML = '<p>Kitap bulunamadı.</p>';
          cardWrapper.classList.remove('visible');
          cardWrapper.classList.add('hidden');

        }
      })
      .catch(error => {
        console.error('Hata:', error);
        cardContainer.innerHTML = '<p>Bir hata oluştu.</p>';
      });
    });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  /********** Ayarlar / seçiciler **********/
  const searchInput = document.querySelector('.search-input');
  const resultsList = document.getElementById('search-results');
  const kategoriBaslik = document.getElementById('kategori-baslik');
  const cardsContainer = document.getElementById('cards-container');
  const cardWrapper = document.getElementById('cardWrapper');

  // Spinner divi (HTML'de ekli olmalı)
  const spinner = document.getElementById('spinner-div');
  if (spinner) {spinner.style.display = 'none';}

  if (!searchInput || !resultsList) {
    console.error('Arama için gerekli temel elementler bulunamadı: .search-input veya #search-results eksik.');
    return;
  }

  const outputContainer = cardsContainer || cardWrapper || null;
  if (!outputContainer) {
    console.warn('cards-container veya cardWrapper bulunamadı. Kartlar DOM\'a eklenemeyecek, yine de öneriler görüntülenecek.');
  }

  const lastSuggestions = { term: '', kitaplar: [], yazarlar: [], yayinevleri: [] };
  let suspendSuggestions = false;

  /********** Yardımcı fonksiyonlar **********/
  async function safeFetchJson(url, opts = {}) {
    const res = await fetch(url, opts);
    const text = await res.text();
    if (text.trim().startsWith('<')) {
      console.error('Beklenmeyen HTML çıktı alındı:', text.slice(0, 500));
      throw new Error('Sunucudan JSON bekleniyordu ama HTML döndü. PHP hata/uyarı olabilir.');
    }
    try { return JSON.parse(text); }
    catch (err) {
      console.error('JSON parse hatası. Raw response:', text.slice(0, 500));
      throw err;
    }
  }

  function decodeHtmlEntities(str) {
    if (!str) return '';
    const txt = document.createElement('textarea');
    txt.innerHTML = str;
    return txt.value;
  }

  function normalizeStr(raw) {
    if (raw === null || raw === undefined) return '';
    let s = String(raw);
    s = decodeHtmlEntities(s);
    s = s.replace(/\u00A0/g, ' ');
    s = s.replace(/[\u200B-\u200D\uFEFF]/g, '');
    s = s.replace(/\s+/g, ' ').trim();
    try { s = s.normalize('NFKC'); } catch (e) {}
    return s;
  }

  function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, s => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"})[s]);
  }

  function makeHeader(type, name) {
    const n = name ? String(name).trim() : '';
    if (type === 'book') return 'Kitaplar';
    if (type === 'author') return n ? `${n} - Yazarın Kitapları` : 'Yazarın Kitapları';
    if (type === 'publisher') return n ? `${n} - Kitapları` : 'Yayınevi - Kitapları';
    return 'Arama Sonuçları';
  }

  /********** Render fonksiyonları **********/
  function renderSuggestions(suggestions) {
    resultsList.innerHTML = '';
    resultsList.style.maxHeight = '170px';
    resultsList.style.overflowY = 'auto';
    let any = false;

    function addGroup(titleText, items, type) {
      if (!items || !items.length) return;
      any = true;
      const grp = document.createElement('div');
      grp.className = 'result-group';
      const title = document.createElement('div');
      title.className = 'result-title';
      title.textContent = titleText;
      grp.appendChild(title);

      items.forEach(obj => {
        const rawName = normalizeStr(obj.BOOK_NAME || obj.AUTHOR_NAME || obj.PUBLISHER || obj.NAME || '');
        const item = document.createElement('div');
        item.className = 'result-item';
        item.dataset.type = type;
        if (obj.BOOK_ID) item.dataset.id = String(obj.BOOK_ID);
        if (obj.AUTHOR_ID) item.dataset.id = String(obj.AUTHOR_ID);
        item.dataset.name = rawName;
        item.textContent = rawName;
        grp.appendChild(item);
      });

      resultsList.appendChild(grp);
    }

    addGroup('Kitaplar', suggestions.kitaplar || [], 'book');
    addGroup('Yazarlar', suggestions.yazarlar || [], 'author');
    addGroup('Yayınevleri', suggestions.yayinevleri || [], 'publisher');

    if (!any) {
      const none = document.createElement('div');
      none.className = 'result-item';
      none.textContent = 'Sonuç bulunamadı';
      resultsList.appendChild(none);
    }
    resultsList.style.display = 'block';
  }

  function renderCards(books, title) {
    if (!outputContainer) return;
    outputContainer.innerHTML = '';

    if (kategoriBaslik) {
      kategoriBaslik.textContent = title || 'Arama Sonuçları';
      kategoriBaslik.style.display = 'block';
      kategoriBaslik.style.marginTop = '170px';
    }

    if (!books || !books.length) {
      outputContainer.innerHTML = `<p>Kitap bulunamadı.</p>`;
      // cards-container ekrana geldiğinde margin-bottom ekle
      try { if (outputContainer) outputContainer.style.marginBottom = '170px'; } catch(e) {}
      return;
    }

    books.forEach(b => {
      const img = b.BOOK_IMAGE || b.BOOKIMAGE || '';
      const name = escapeHtml(b.BOOK_NAME || b.BOOKNAME || '');
      const id = b.BOOK_ID || b.ID || '';
      const categoryText = escapeHtml(b.CATEGORY_NAME || b.DESCRIPTION || b.CATEGORY_ID || '');
      const card = document.createElement('div');
      card.className = 'card9';
      card.innerHTML = `
        <form action="bilgiler.php" method="POST" style="margin:0;padding:0;">
          <button class="see9" type="submit" name="kitap-detay" value="${id}">
            <img src="${img}" alt="${name}" />
          </button>
        </form>
        <h2 class="card-h9">${name}</h2>
        <form class="card-form9" action="" method="POST">
          <button class="i" type="button"><h2 translate="no">i</h2><div class="tooltip">${categoryText}</div></button>
          <button class="kalp-button" type="button" name="kitap-ID" value="${id}">
            <i class="fa-regular fa-heart" style="font-size: 20px; margin-top: 3px;"></i>
          </button>
          <button class="OKL-ekle" type="button"><i class="fa-solid fa-check"></i></button>
        </form>
      `;
      outputContainer.appendChild(card);
    });

    outputContainer.style.display = 'flex';
    outputContainer.style.flexWrap = 'wrap';
    outputContainer.style.gap = '28px';
    // cards-container ekrana geldiğinde margin-bottom ekle
    try { if (outputContainer) outputContainer.style.marginBottom = '170px'; } catch(e) {}
  }

  /********** Arama mantığı **********/
  async function performSearchByTerm(rawTerm) {
    const term = normalizeStr(rawTerm);
    if (!term) {
      resultsList.style.display = 'none';
      return;
    }

    // Spinner göster
    if (spinner) {spinner.style.display = 'flex'; spinner.style.justifyContent = 'center';}
    resultsList.style.display = 'none'; // öneri kutusunu kapat

    try {
      const data = await safeFetchJson(`search.php?term=${encodeURIComponent(term)}`);
      const kitaplar = data.kitaplar || data.books || [];
      const yazarlar = data.yazarlar || data.authors || [];
      const yayinevleri = data.yayinevleri || data.publishers || data.yayincilar || [];

      lastSuggestions.term = term;
      lastSuggestions.kitaplar = kitaplar;
      lastSuggestions.yazarlar = yazarlar;
      lastSuggestions.yayinevleri = yayinevleri;

      if (kitaplar && kitaplar.length > 0) {
        const exact = kitaplar.filter(b => normalizeStr(b.BOOK_NAME) === term);
        if (exact && exact.length > 0) {
          renderCards(exact, makeHeader('book', ''));
          return;
        }
        renderCards(kitaplar, makeHeader('book',''));
        return;
      }

      if (yazarlar && yazarlar.length > 0) {
        const firstAuthor = yazarlar[0];
        const authorId = firstAuthor.AUTHOR_ID || firstAuthor.AUTHORID || null;
        const authorName = normalizeStr(firstAuthor.AUTHOR_NAME || firstAuthor.NAME || term);
        let books = [];
        if (authorId) {
          try {
            books = await safeFetchJson(`search.php?type=author_books&author_id=${encodeURIComponent(authorId)}`);
          } catch(err) {}
        }
        renderCards(books, makeHeader('author', authorName));
        return;
      }

      if (yayinevleri && yayinevleri.length > 0) {
        const pubName = normalizeStr(yayinevleri[0].PUBLISHER || yayinevleri[0].NAME || term);
        let books = [];
        try {
          books = await safeFetchJson(`search.php?type=publisher_books&publisher=${encodeURIComponent(pubName)}`);
        } catch(err) {}
        renderCards(books, makeHeader('publisher', pubName));
        return;
      }

      if (outputContainer) {
        outputContainer.innerHTML = '<p>Aradığınız kitap/yazar/yayınevi bulunamadı.</p>';
        // bulunamadı mesajı da cards-container olarak sayfada göründüğünde margin-bottom ekle
        try { outputContainer.style.marginBottom = '170px'; } catch(e) {}
      }
    } catch (err) {
      console.error('performSearchByTerm hatası:', err);
      if (outputContainer) outputContainer.innerHTML = '<p>Arama sırasında hata oluştu.</p>';
    } finally {
      // Spinner gizle
      if (spinner) spinner.style.display = 'none';
      resultsList.style.display = 'none';
    }
  }

  /********** Event bağlamaları **********/
  let inputTimeout = null;
  searchInput.addEventListener('input', () => {
    if (suspendSuggestions) return;

    const term = normalizeStr(searchInput.value);
    if (!term) {
      resultsList.innerHTML = '';
      resultsList.style.display = 'none';
      lastSuggestions.term = '';
      lastSuggestions.kitaplar = [];
      lastSuggestions.yazarlar = [];
      lastSuggestions.yayinevleri = [];
      // arama temizlendiğinde cards-container marginBottom'ı kaldır
      try { if (outputContainer) outputContainer.style.marginBottom = ''; } catch(e) {}
      return;
    }

    clearTimeout(inputTimeout);
    inputTimeout = setTimeout(async () => {
      try {
        // Spinner göster
        if (spinner) {spinner.style.display = 'flex'; spinner.style.justifyContent = 'center';}

        const data = await safeFetchJson(`search.php?term=${encodeURIComponent(term)}`);
        const kitaplar = data.kitaplar || data.books || [];
        const yazarlar = data.yazarlar || data.authors || [];
        const yayinevleri = data.yayinevleri || data.publishers || data.yayincilar || [];

        lastSuggestions.term = term;
        lastSuggestions.kitaplar = kitaplar;
        lastSuggestions.yazarlar = yazarlar;
        lastSuggestions.yayinevleri = yayinevleri;

        renderSuggestions({kitaplar, yazarlar, yayinevleri});
      } catch (err) {
        console.error('Öneri alınırken hata:', err);
        resultsList.innerHTML = `<div class="result-item">Sunucu hatası, konsolu kontrol et.</div>`;
        resultsList.style.display = 'block';
      } finally {
        // Spinner gizle
        if (spinner) spinner.style.display = 'none';
      }
    }, 220);
  });

  searchInput.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const term = normalizeStr(searchInput.value);
    if (!term) return;
    performSearchByTerm(term);
  });

  resultsList.addEventListener('click', async (e) => {
    const item = e.target.closest('.result-item');
    if (!item) return;

    suspendSuggestions = true;
    const visibleText = normalizeStr(item.textContent || item.dataset.name || '');

    searchInput.value = visibleText;
    searchInput.focus();
    resultsList.style.display = 'none';

    try {
      await performSearchByTerm(visibleText);
    } catch(err) {
      console.error('Click ile arama sırasında hata:', err);
    } finally {
      suspendSuggestions = false;
      resultsList.style.display = 'none';
    }
  });

  (function injectSmallStyles(){
    if (!resultsList) return;
    resultsList.style.boxSizing = 'border-box';
    resultsList.style.background = '#fff';
    resultsList.style.border = '1px solid rgba(0,0,0,0.08)';
    resultsList.style.borderRadius = '6px';
    resultsList.style.padding = '8px';
    resultsList.style.display = 'none';
  })();

});
</script>


    



</body>
</html>