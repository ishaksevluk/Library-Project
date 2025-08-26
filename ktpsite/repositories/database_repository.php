<?php

function getConnection() {
    $kullanici = 'ifsapp';
    $sifre = '809qeKgwwLYqd7Eo5BISV6QZXJBo2l';
    $host = 'poziq7y-dev1-db.build.ifs.cloud';
    $port = '1521';
    $servis_adi = 'alepdb';

    $tns = "
     (DESCRIPTION =
      (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))
      (CONNECT_DATA =
          (SERVICE_NAME = $servis_adi)
      )
     )
    ";

    $conn = oci_connect($kullanici, $sifre, $tns, 'AL32UTF8');
    if (!$conn) {
        $error = oci_error();
        die('Veritabanına bağlanılamadı: ' . $error['message']);
    }
    return $conn;
}

// 📚 Kategori arayüzü
interface CategoryRepositoryInterface {
    public function getAll(): array;
    public function getById(string $id): ?array;
}

// 📘 Kitap arayüzü
interface BookRepositoryInterface {
    public function getAll(): array;
    public function getById(string $id): ?array;
}

// 🍊 Oracle kategori repository
class OracleCategoryRepository implements CategoryRepositoryInterface {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT CATEGORY_ID, DESCRIPTION FROM F8LIB_CATEGORIES";
        $stid = oci_parse($this->conn, $sql);
        oci_execute($stid);

        $results = [];
        while (($row = oci_fetch_assoc($stid)) !== false) {
            $results[] = $row;
        }
        oci_free_statement($stid);
        return $results;
    }

    public function getById(string $id): ?array {
        $sql = "SELECT CATEGORY_ID, DESCRIPTION FROM F8LIB_CATEGORIES WHERE CATEGORY_ID = :id";
        $stid = oci_parse($this->conn, $sql);
        oci_bind_by_name($stid, ':id', $id);
        oci_execute($stid);

        $row = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        return $row !== false ? $row : null;
    }
}

// 📚 Oracle kitap repository
class OracleBookRepository implements BookRepositoryInterface {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT BOOK_ID, BOOK_NAME, AUTHOR_ID, PUBLISHER, ISBN, PUBLISH_YEAR, CATEGORY_ID, BOOK_IMAGE, BOOK_SUMMARY, ADDED_DATE, PAGE_COUNT FROM F8LIB_BOOKS";
        $stid = oci_parse($this->conn, $sql);
        oci_execute($stid);

        $books = [];
        while (($row = oci_fetch_assoc($stid)) !== false) {
            $books[] = $row;
        }
        oci_free_statement($stid);
        return $books;
    }

    public function getById(string $id): ?array {
        $sql = "SELECT BOOK_ID, BOOK_NAME, AUTHOR_ID, PUBLISHER, ISBN, PUBLISH_YEAR, CATEGORY_ID, BOOK_IMAGE, BOOK_SUMMARY, ADDED_DATE, STATE FROM F8LIB_BOOKS WHERE BOOK_ID = :id";
        $stid = oci_parse($this->conn, $sql);
        oci_bind_by_name($stid, ':id', $id);
        oci_execute($stid);

        $row = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        return $row !== false ? $row : null;
    }

    public function getByCategoryId(string $categoryId): array {
      $sql = "SELECT BOOK_ID, BOOK_NAME, AUTHOR_ID, PUBLISHER, ISBN, PUBLISH_YEAR, CATEGORY_ID, BOOK_IMAGE, BOOK_SUMMARY 
            FROM F8LIB_BOOKS WHERE CATEGORY_ID = :categoryId";
      $stid = oci_parse($this->conn, $sql);
      oci_bind_by_name($stid, ':categoryId', $categoryId);
      oci_execute($stid);

      $books = [];
      while (($row = oci_fetch_assoc($stid)) !== false) {
        $books[] = $row;
      }
      oci_free_statement($stid);
      return $books;
   }

   public function search(string $keyword): array {
    $sql = "SELECT BOOK_ID, BOOK_NAME, AUTHOR_ID, PUBLISHER
            FROM F8LIB_BOOKS
            WHERE LOWER(BOOK_NAME) LIKE LOWER(:kw)
               OR LOWER(AUTHOR_ID) LIKE LOWER(:kw)
               OR LOWER(PUBLISHER) LIKE LOWER(:kw)
            ORDER BY 
                CASE 
                    WHEN LOWER(BOOK_NAME) LIKE LOWER(:kw) THEN 1
                    WHEN LOWER(AUTHOR_ID) LIKE LOWER(:kw) THEN 2
                    ELSE 3
                END";
    
    $stid = oci_parse($this->conn, $sql);
    $kwLike = "%" . $keyword . "%";
    oci_bind_by_name($stid, ':kw', $kwLike);
    oci_execute($stid);

    $results = [];
    while (($row = oci_fetch_assoc($stid)) !== false) {
        $results[] = $row;
    }
    oci_free_statement($stid);
    return $results;
    }

}

// 🔔 Rezervasyon fonksiyonu: burada getConnection() zaten tanımlı olduğu için sorun olmaz
function addReservation(string $bookId, string $userId): array {
    $conn = getConnection();

    $sql = "BEGIN F8lib_Reserve_Books_Util_API.Add_Reservation(:book_id, :user_id); END;";
    $stid = oci_parse($conn, $sql);

    oci_bind_by_name($stid, ':book_id', $bookId);
    oci_bind_by_name($stid, ':user_id', $userId);

    if (oci_execute($stid)) {
        oci_free_statement($stid);
        oci_close($conn);
        return ['success' => true, 'message' => 'Rezervasyon başarıyla eklendi!'];
    } else {
        $err = oci_error($stid);
        oci_free_statement($stid);
        oci_close($conn);
        return ['success' => false, 'message' => $err['message'] ?? 'Bilinmeyen hata'];
    }
}

function getUserByLoginDetailed($email, $password) {
    try {
        $conn = getConnection();

        // 1) Kullanıcıyı sadece email ile çek (activation ve password kontrolü olmadan)
        $sql = "
            SELECT USER_ID, NAME, EMAIL, PASSWORD, USER_ACTIVATION
              FROM F8LIB_USERS
             WHERE LOWER(EMAIL) = LOWER(:email)
               AND ROWNUM = 1
        ";

        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':email', $email, 4000, SQLT_CHR);

        $ok = @oci_execute($stid);
        if (!$ok) {
            $err = oci_error($stid) ?: oci_error($conn);
            oci_free_statement($stid);
            oci_close($conn);
            error_log('Login select execute error: ' . print_r($err, true));
            return ['status' => 'error', 'error' => 'db_execute_error'];
        }

        $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
        oci_free_statement($stid);
        oci_close($conn);

        if (!$row) {
            // email bulunamadı
            return ['status' => 'no_user'];
        }

        // Activation kontrolü: hem '1' hem 1 şeklinde olabilir; esnek kontrol
        $activation = isset($row['USER_ACTIVATION']) ? trim((string)$row['USER_ACTIVATION']) : '';
        if ($activation !== '1' && strtolower($activation) !== 'y' && strtolower($activation) !== 'true') {
            return ['status' => 'inactive', 'user' => $row];
        }

        // Şifre kontrolü: olası çeşitli saklama formatları için birkaç kontrol
        $dbPassword = isset($row['PASSWORD']) ? trim((string)$row['PASSWORD']) : '';

        $matched = false;

        // 1) Eğer veritabanında hashlenmiş password varsa password_verify deneyelim
        if ($dbPassword !== '' && function_exists('password_verify')) {
            // password_verify() hata vermez, eğer $dbPassword hash değilse false döner
            if (@password_verify($password, $dbPassword)) {
                $matched = true;
            }
        }

        // 2) Plain-text karşılaştırma (TRIM uygulanmış hali)
        if (!$matched) {
            if ($dbPassword === $password) {
                $matched = true;
            }
        }

        // 3) md5 kontrolü (eğer eski bir sistem md5 saklıyorsa)
        if (!$matched && strlen($dbPassword) === 32) {
            if (strtolower($dbPassword) === md5($password)) {
                $matched = true;
            }
        }

        // 4) sha1 kontrolü (opsiyonel)
        if (!$matched && strlen($dbPassword) === 40) {
            if (strtolower($dbPassword) === sha1($password)) {
                $matched = true;
            }
        }

        if ($matched) {
            return ['status' => 'ok', 'user' => $row];
        } else {
            return ['status' => 'wrong_password', 'user' => $row];
        }

    } catch (Exception $e) {
        error_log('getUserByLoginDetailed exception: ' . $e->getMessage());
        return ['status' => 'error', 'error' => 'exception'];
    }
}





//YENİ EKLENEN KODLAR
/** Helper: fetch all associative rows */
function fetchAllAssoc($stid): array {
    $rows = [];
    while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) !== false) {
        $rows[] = $row;
    }
    return $rows;
}

/** getBooksByIds */
function getBooksByIds(array $ids): array {
    if (empty($ids)) return [];
    $ids = array_map('intval', $ids);
    $inList = implode(',', $ids);

    try {
        $conn = getConnection();
        $sql = "SELECT * FROM F8LIB_BOOKS WHERE BOOK_ID IN ({$inList})";
        $stid = @oci_parse($conn, $sql);
        if (!$stid) { error_log('getBooksByIds parse error: ' . print_r(oci_error($conn), true)); oci_close($conn); return []; }
        $ok = @oci_execute($stid);
        if (!$ok) { error_log('getBooksByIds execute error: ' . print_r(oci_error($stid) ?: oci_error($conn), true)); oci_free_statement($stid); oci_close($conn); return []; }
        $rows = fetchAllAssoc($stid);
        oci_free_statement($stid);
        oci_close($conn);
        return $rows;
    } catch (Exception $e) {
        error_log('getBooksByIds exception: ' . $e->getMessage());
        return [];
    }
}

/** getFeaturedBooks */
function getFeaturedBooks(int $limit = 8): array {
    $limit = max(1, (int)$limit);
    try {
        $conn = getConnection();
        $sqlIds = "
            SELECT DISTINCT BOOK_ID
              FROM (
                SELECT BOOK_ID, RETURN_DATE
                  FROM F8LIB_BORROW_RECORDS
                 WHERE RETURN_DATE IS NOT NULL
                 ORDER BY RETURN_DATE DESC
              )
             WHERE ROWNUM <= {$limit}
        ";
        $stidIds = @oci_parse($conn, $sqlIds);
        if (!$stidIds) { error_log('getFeaturedBooks parse error: ' . print_r(oci_error($conn), true)); oci_close($conn); return []; }
        $ok = @oci_execute($stidIds);
        if (!$ok) { error_log('getFeaturedBooks execute error: ' . print_r(oci_error($stidIds) ?: oci_error($conn), true)); oci_free_statement($stidIds); oci_close($conn); return []; }
        $ids = [];
        while (($row = oci_fetch_array($stidIds, OCI_NUM + OCI_RETURN_NULLS)) !== false) {
            if (isset($row[0]) && $row[0] !== null && $row[0] !== '') $ids[] = (int)$row[0];
        }
        oci_free_statement($stidIds);
        oci_close($conn);
        if (empty($ids)) return [];
        return getBooksByIds($ids);
    } catch (Exception $e) {
        error_log('getFeaturedBooks exception: ' . $e->getMessage());
        return [];
    }
}

/** getNewArrivals */
function getNewArrivals(int $limit = 8): array {
    $limit = max(1, (int)$limit);
    try {
        $conn = getConnection();
        $sql = "
            SELECT *
              FROM (
                SELECT *
                  FROM F8LIB_BOOKS
                 ORDER BY ADDED_DATE DESC
              )
             WHERE ROWNUM <= {$limit}
        ";
        $stid = @oci_parse($conn, $sql);
        if (!$stid) { error_log('getNewArrivals parse error: ' . print_r(oci_error($conn), true)); oci_close($conn); return []; }
        $ok = @oci_execute($stid);
        if (!$ok) { error_log('getNewArrivals execute error: ' . print_r(oci_error($stid) ?: oci_error($conn), true)); oci_free_statement($stid); oci_close($conn); return []; }
        $rows = fetchAllAssoc($stid);
        oci_free_statement($stid);
        oci_close($conn);
        return $rows;
    } catch (Exception $e) {
        error_log('getNewArrivals exception: ' . $e->getMessage());
        return [];
    }
}

/** getBookById */
function getBookById(int $bookId): ?array {
    if ($bookId <= 0) return null;
    try {
        $conn = getConnection();
        $sql = "SELECT * FROM F8LIB_BOOKS WHERE BOOK_ID = :bookId";
        $stid = @oci_parse($conn, $sql);
        if (!$stid) { error_log('getBookById parse error: ' . print_r(oci_error($conn), true)); oci_close($conn); return null; }
        oci_bind_by_name($stid, ':bookId', $bookId, -1);
        $ok = @oci_execute($stid);
        if (!$ok) { error_log('getBookById execute error: ' . print_r(oci_error($stid) ?: oci_error($conn), true)); oci_free_statement($stid); oci_close($conn); return null; }
        $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
        oci_free_statement($stid);
        oci_close($conn);
        return $row ?: null;
    } catch (Exception $e) {
        error_log('getBookById exception: ' . $e->getMessage());
        return null;
    }
}


function getIsbnByBookId(int $bookId): ?string {
    if ($bookId <= 0) return null;

    $tableCandidates = [
        'F8LIB_BOOKS',
        'IFSAPP.F8LIB_BOOKS',
        'BOOKS'
    ];
    $colCandidates = ['ISBN', 'BOOK_ISBN', 'ISBN13', 'ISBN_13', 'EAN'];

    foreach ($tableCandidates as $tbl) {
        foreach ($colCandidates as $col) {
            try {
                $conn = getConnection();
                $sql = "SELECT {$col} AS ISBN_COL FROM {$tbl} WHERE BOOK_ID = :bid AND ROWNUM = 1";
                $stid = @oci_parse($conn, $sql);
                if (!$stid) { oci_close($conn); continue; }
                oci_bind_by_name($stid, ':bid', $bookId, -1);
                $ok = @oci_execute($stid);
                if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
                $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
                oci_free_statement($stid);
                oci_close($conn);
                if ($row && array_key_exists('ISBN_COL', $row) && $row['ISBN_COL'] !== null && $row['ISBN_COL'] !== '') {
                    return (string)$row['ISBN_COL'];
                }
            } catch (Exception $e) {
                error_log("getIsbnByBookId try {$tbl}.{$col} exception: " . $e->getMessage());
                // devam et
            }
        }
    }
    return null;
}









function getUserByLoginDetaileds(string $email, string $password): array
{
    try {
        $conn = getConnection();

        // Kullanıcı bilgilerini çek (örnek alan adları: USER_ID, EMAIL, PASSWORD_HASH, STATUS)
        $sql = "SELECT USER_ID, EMAIL, PASSWORD, STATUS
                FROM F8LIB_USERS
                WHERE LOWER(EMAIL) = LOWER(:email)";

        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':email', $email);
        oci_execute($stid);

        $row = oci_fetch_assoc($stid);

        oci_free_statement($stid);
        oci_close($conn);

        if (!$row) {
            return ['status' => 'no_user'];
        }

        // "STATUS" kontrolü - örnek: 'ACTIVE' ya da 1 olabilir; projeye göre uyarlayın
        $status = $row['STATUS'] ?? null;
        if ($status !== null) {
            $active = false;
            // olası durumları kontrol etmek için basit mantık:
            if (is_numeric($status)) {
                $active = ((int)$status === 1);
            } else {
                $active = (strtoupper(trim((string)$status)) === 'ACTIVE' || strtoupper(trim((string)$status)) === 'A');
            }
            if (!$active) {
                return ['status' => 'inactive'];
            }
        }

        //$dbHash = $row['PASSWORD_HASH'] ?? null;
        $dbPlain = $row['PASSWORD'] ?? null;

        $passwordOk = false;
        if ($dbHash && password_verify($password, $dbHash)) {
            $passwordOk = true;
        } elseif ($dbPlain !== null && $password === $dbPlain) {
            // Eğer sistemde eski düz metin parola varsa (geçiş için)
            $passwordOk = true;
        }

        if (!$passwordOk) {
            return ['status' => 'wrong_password'];
        }

        // Başarılı login: kullanıcı verisini döndür
        $user = [
            'USER_ID' => $row['USER_ID'],
            'EMAIL' => $row['EMAIL'],
            // istersen diğer alanları ekle
        ];

        return ['status' => 'ok', 'user' => $user];

    } catch (Exception $e) {
        error_log('getUserByLoginDetaileds hata: ' . $e->getMessage());
        return ['status' => 'error'];
    }
}







function getAuthors(string $authorname): array
{
    $sql = "SELECT AUTHOR_NAME 
            FROM F8LIB_AUTHORS
            WHERE AUTHOR_NAME = :author_name";

    $stid = oci_parse($this->conn, $sql);
    oci_bind_by_name($stid, ':author_name', $authorname);
    oci_execute($stid);

    $authors = [];
    while (($row = oci_fetch_assoc($stid)) !== false) {
        $authors[] = $row['AUTHOR_NAME'];
    }

    oci_free_statement($stid);
    return $authors;
}
   



function getUserById2(string $userId): ?array
{
    // $this->conn varsayılan OCI bağlantın olmalı
    $sql = "SELECT USER_ID, NAME, EMAIL, CREATION_DATE FROM F8LIB_USERS WHERE USER_ID = :uid";
    $stid = @oci_parse($this->conn, $sql);
    if (!$stid) {
        // parse hatası varsa null dön
        return null;
    }

    // bind (string olarak gönderiyoruz)
    oci_bind_by_name($stid, ':uid', $userId);

    $ok = @oci_execute($stid);
    if (!$ok) {
        // execute hatasıysa statement serbest bırakıp null dön
        oci_free_statement($stid);
        return null;
    }

    $row = oci_fetch_assoc($stid);
    oci_free_statement($stid);

    return $row !== false ? $row : null;
}


function getUserProfileById(int $userId): ?array {
    if ($userId <= 0) return null;
    $tables = ['F8LIB_USERS', 'IFSAPP.F8LIB_USERS'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            $sql = "SELECT USER_ID, NAME, EMAIL, TO_CHAR(CREATION_DATE, 'YYYY-MM-DD HH24:MI:SS') AS CREATION_DATE FROM {$tbl} WHERE USER_ID = :uid";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':uid', $userId);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row) return $row;
        } catch (Exception $e) {
            error_log("getUserProfileById using {$tbl} exception: " . $e->getMessage());
            // diğer tabloyla denemeye devam et
        }
    }
    return null;
}

function getUserProfileByEmail(string $email): ?array {
    $email = trim($email);
    if ($email === '') return null;
    $tables = ['F8LIB_USERS', 'IFSAPP.F8LIB_USERS'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            $sql = "SELECT USER_ID, NAME, EMAIL, TO_CHAR(CREATION_DATE, 'YYYY-MM-DD HH24:MI:SS') AS CREATION_DATE FROM {$tbl} WHERE LOWER(EMAIL) = LOWER(:em) AND ROWNUM = 1";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':em', $email, 4000, SQLT_CHR);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row) return $row;
        } catch (Exception $e) {
            error_log("getUserProfileByEmail using {$tbl} exception: " . $e->getMessage());
            // diğer tabloyla denemeye devam et
        }
    }
    return null;
}

/**
 * Kategori ID'den açıklamayı döndürür (DESCRIPTION).
 */
function getCategoryDescriptionById(string $categoryId): ?string {
    $categoryId = trim($categoryId);
    if ($categoryId === '') return null;
    $tables = ['F8LIB_CATEGORIES', 'IFSAPP.F8LIB_CATEGORIES'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            $sql = "SELECT DESCRIPTION FROM {$tbl} WHERE CATEGORY_ID = :cid";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':cid', $categoryId);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row && isset($row['DESCRIPTION'])) {
                return (string)$row['DESCRIPTION'];
            }
        } catch (Exception $e) {
            error_log("getCategoryDescriptionById using {$tbl} exception: " . $e->getMessage());
        }
    }
    return null;
}

/**
 * Yazar ID'den yazar adını döndürür (AUTHOR_NAME).
 */
function getAuthorNameById(string $authorId): ?string {
    $authorId = trim($authorId);
    if ($authorId === '') return null;
    $tables = ['F8LIB_AUTHORS', 'IFSAPP.F8LIB_AUTHORS'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            $sql = "SELECT AUTHOR_NAME FROM {$tbl} WHERE AUTHOR_ID = :aid";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':aid', $authorId);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row && isset($row['AUTHOR_NAME'])) {
                return (string)$row['AUTHOR_NAME'];
            }
        } catch (Exception $e) {
            error_log("getAuthorNameById using {$tbl} exception: " . $e->getMessage());
        }
    }
    return null;
}

/**
 * Kitabın şu anda müsait olup olmadığını döndürür.
 * Mantık: F8LIB_BORROW_RECORDS tablosunda ilgili BOOK_ID için RETURN_DATE IS NULL kayıt varsa kitap kullanıcıdadır (OnReader) → false.
 */
function isBookAvailable(int $bookId): bool {
    if ($bookId <= 0) return false;
    $tables = ['F8LIB_BORROW_RECORDS', 'IFSAPP.F8LIB_BORROW_RECORDS'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            $sql = "SELECT COUNT(1) AS CNT FROM {$tbl} WHERE BOOK_ID = :bid AND RETURN_DATE IS NULL";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':bid', $bookId);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row && isset($row['CNT'])) {
                return ((int)$row['CNT']) === 0; 
            }
        } catch (Exception $e) {
            error_log("isBookAvailable using {$tbl} exception: " . $e->getMessage());
        }
    }
    // tabloya erişilemediyse emin olamıyorsak konservatif davranıp false dönebiliriz; burada true döndürüyoruz
    return true;
}

/**
 * Kullanıcının belirli bir kitap için son rezervasyon durumunu döndürür.
 * Dönen değer örn: 'waiting', 'active', 'finish' vb. Yoksa null.
 */
function getReservationState(int $userId, int $bookId): ?string {
    if ($userId <= 0 || $bookId <= 0) return null;
    $tables = ['F8LIB_RESERVATIONS', 'IFSAPP.F8LIB_RESERVATIONS'];
    foreach ($tables as $tbl) {
        try {
            $conn = getConnection();
            // En güncel kaydı almak için tarih alanı varsa ona göre sırala; yoksa ilk bulunanı al
            $sql = "SELECT STATE FROM (SELECT STATE FROM {$tbl} WHERE USER_ID = :uid AND BOOK_ID = :bid ORDER BY RESERVATION_DATE DESC) WHERE ROWNUM = 1";
            $stid = @oci_parse($conn, $sql);
            if (!$stid) { oci_close($conn); continue; }
            oci_bind_by_name($stid, ':uid', $userId);
            oci_bind_by_name($stid, ':bid', $bookId);
            $ok = @oci_execute($stid);
            if (!$ok) { oci_free_statement($stid); oci_close($conn); continue; }
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            oci_free_statement($stid);
            oci_close($conn);
            if ($row && isset($row['STATE']) && $row['STATE'] !== null && $row['STATE'] !== '') {
                return (string)$row['STATE'];
            }
        } catch (Exception $e) {
            error_log("getReservationState using {$tbl} exception: " . $e->getMessage());
        }
    }
    return null;
}

/**
 * Oturum tarafindaki rezerve kilidini (sayfa yenileyince disabled kalmasin diye) temizler.
 */
function clearReserveLockForBook(int $bookId): void {
    if (!isset($_SESSION['reserve_lock']) || !is_array($_SESSION['reserve_lock'])) {
        return;
    }
    unset($_SESSION['reserve_lock'][$bookId]);
    if (empty($_SESSION['reserve_lock'])) {
        unset($_SESSION['reserve_lock']);
    }
}

?>