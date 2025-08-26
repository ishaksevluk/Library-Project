<?php
// toggle_favorite.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

// geliştirme ortamında hata çıktısı kapat: production'da display_errors = 0
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once 'repositories/database_repository.php';

try {
    // doğrulamalar
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId <= 0) {
        echo json_encode(['ok' => 0, 'msg' => 'login_required']);
        exit;
    }

    $bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    $isFavRaw = isset($_POST['is_favorite']) ? (string)$_POST['is_favorite'] : '';
    if ($bookId <= 0) {
        echo json_encode(['ok' => 0, 'msg' => 'invalid_book_id']);
        exit;
    }
    if ($isFavRaw !== '0' && $isFavRaw !== '1') {
        echo json_encode(['ok' => 0, 'msg' => 'invalid_is_favorite']);
        exit;
    }
    $isFavorite = ($isFavRaw === '1') ? 1 : 0;

    $conn = getConnection();

    // 1) mevcut kaydı güncelle
    $sqlUpdate = "UPDATE F8LIB_FAVORITES SET IS_FAVORITE = :isfav WHERE USER_ID = :uid AND BOOK_ID = :bid";
    $stid = oci_parse($conn, $sqlUpdate);
    oci_bind_by_name($stid, ':isfav', $isFavorite, -1, SQLT_INT);
    oci_bind_by_name($stid, ':uid', $userId, -1, SQLT_INT);
    oci_bind_by_name($stid, ':bid', $bookId, -1, SQLT_INT);
    $ok = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
    $rowsAffected = $ok ? oci_num_rows($stid) : 0;
    oci_free_statement($stid);

    // 2) Eğer güncellenecek satır yoksa ve isFavorite=1 ise insert et; isFavorite=0 ise hiçbir şey yapma
    if ($rowsAffected === 0 && $isFavorite === 1) {
        $sqlInsert = "INSERT INTO F8LIB_FAVORITES (USER_ID, BOOK_ID, IS_FAVORITE) VALUES (:uid, :bid, :isfav)";
        $stid2 = oci_parse($conn, $sqlInsert);
        oci_bind_by_name($stid2, ':uid', $userId, -1, SQLT_INT);
        oci_bind_by_name($stid2, ':bid', $bookId, -1, SQLT_INT);
        oci_bind_by_name($stid2, ':isfav', $isFavorite, -1, SQLT_INT);
        $okIns = @oci_execute($stid2, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stid2);
        if (!$okIns) {
            $err = oci_error($stid2) ?: oci_error($conn);
            error_log('favori insert hata: ' . print_r($err, true));
            oci_close($conn);
            echo json_encode(['ok' => 0, 'msg' => 'database_error']);
            exit;
        }
    }

    oci_close($conn);

    // Başarıyla dön — isFavorite olarak DB'deki son durumu döndür (istemci bunu kullanır)
    echo json_encode(['ok' => 1, 'isFavorite' => $isFavorite ? '1' : '0']);
    exit;

} catch (Throwable $e) {
    error_log('toggle_favorite istisna: ' . $e->getMessage());
    // ayrıntılı hata mesajı kullanıcıya verme, sadece genel bilgi dön
    echo json_encode(['ok' => 0, 'msg' => 'server_error']);
    exit;
}
