<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'repositories/database_repository.php';
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => 0, 'msg' => 'method_not_allowed']);
    exit;
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode(['ok' => 0, 'msg' => 'login_required']);
    exit;
}

$bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
// Accept alternate key names and normalize unicode whitespace
$rawComment = isset($_POST['comment']) ? (string)$_POST['comment'] : (isset($_POST['review_comment']) ? (string)$_POST['review_comment'] : '');
// Replace common unicode spaces and zero-width chars
$comment = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], ' ', $rawComment);
$comment = preg_replace('/\s+/u', ' ', $comment);
$comment = trim($comment);

if ($bookId <= 0) { echo json_encode(['ok' => 0, 'msg' => 'invalid_book_id']); exit; }
if ($rating < 1 || $rating > 5) { echo json_encode(['ok' => 0, 'msg' => 'invalid_rating']); exit; }
// Frontend ana sayfa ve bilgiler.php'deki anahtarlarla uyumlu hata kodları
if ($comment === '') { echo json_encode(['ok' => 0, 'msg' => 'review_comment_required']); exit; }
if (mb_strlen($comment, 'UTF-8') > 800) { echo json_encode(['ok' => 0, 'msg' => 'review_comment_too_long']); exit; }

function tryCallPackage($conn, $procFullName, $bookId, $userId, $comment, $rating) {
    $plsql = "BEGIN {$procFullName}(:p_book_id, :p_user_id, :p_review_comment, :p_rating); END;";
    $stid = oci_parse($conn, $plsql);
    if (!$stid) { return [false, oci_error($conn)]; }
    oci_bind_by_name($stid, ':p_book_id', $bookId, -1, SQLT_INT);
    oci_bind_by_name($stid, ':p_user_id', $userId, -1, SQLT_INT);
    oci_bind_by_name($stid, ':p_review_comment', $comment, 4000, SQLT_CHR);
    oci_bind_by_name($stid, ':p_rating', $rating, -1, SQLT_INT);
    $ok = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
    $err = $ok ? null : (oci_error($stid) ?: oci_error($conn));
    @oci_free_statement($stid);
    return [$ok, $err];
}

try {
    $conn = getConnection();

    $candidates = [
        'F8lib_Book_Reviews_API.Add_Book_Reviews',
        'IFSAPP.F8lib_Book_Reviews_API.Add_Book_Reviews'
    ];

    $ok = false; $lastErr = null;
    foreach ($candidates as $proc) {
        list($okCall, $err) = tryCallPackage($conn, $proc, $bookId, $userId, $comment, $rating);
        if ($okCall) { $ok = true; break; }
        $lastErr = $err;
    }

    @oci_close($conn);

    if ($ok) {
        // UI'da yeni yoruma anında isim gösterebilmek için kullanıcı adını ekle
        $profile = getUserProfileById($userId);
        $userName = ($profile && isset($profile['NAME']) && $profile['NAME'] !== null && $profile['NAME'] !== '') ? (string)$profile['NAME'] : 'Kullanıcı';
        echo json_encode([
            'ok' => 1,
            'msg' => 'Yorumunuz başarıyla gönderildi.',
            'data' => [ 'user_name' => $userName ]
        ]);
    } else {
        error_log('yorum_ekle package hatası: ' . print_r($lastErr, true));
        echo json_encode(['ok' => 0, 'msg' => 'database_error']);
    }

} catch (Throwable $e) {
    error_log('Yorum ekleme istisna: ' . $e->getMessage());
    echo json_encode(['ok' => 0, 'msg' => 'server_error']);
}
?>