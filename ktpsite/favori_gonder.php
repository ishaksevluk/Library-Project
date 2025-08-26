<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'repositories/database_repository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => 0, 'msg' => 'method_not_allowed']);
    exit;
}

// POST öncelikli, yoksa session'dan düş
$bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : (int)($_SESSION['book_id'] ?? 0);
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : (int)($_SESSION['user_id'] ?? 0);
$isFavorite = isset($_POST['is_favorite']) ? ((int)$_POST['is_favorite'] ? 1 : 0) : 1; // varsayılan ekleme

if ($bookId <= 0 || $userId <= 0) {
    echo json_encode(['ok' => 0, 'msg' => 'invalid_params']);
    exit;
}

// Session'ı da senkron tut (opsiyonel)
$_SESSION['book_id'] = $bookId;
$_SESSION['user_id'] = $userId;
$_SESSION['is_favorite'] = $isFavorite;
$isFavorite = 1;

try {
    $conn = getConnection();

    $sql = "BEGIN IFSAPP.F8lib_Favorites_API.Add_Favorites (:book_id, :user_id, :is_favorite); END;";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':book_id', $bookId);
    oci_bind_by_name($stid, ':user_id', $userId);
    oci_bind_by_name($stid, ':is_favorite', $isFavorite);

    $ok = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
    if ($ok) {
        echo json_encode(['ok' => 1, 'isFavorite' => $isFavorite]);
    } else {
        $err = oci_error($stid) ?: oci_error($conn);
        echo json_encode(['ok' => 0, 'msg' => ($err['message'] ?? 'db_error')]);
    }

    @oci_free_statement($stid);
    @oci_close($conn);

} catch (Throwable $e) {
    echo json_encode(['ok' => 0, 'msg' => 'server_error']);
}
?>