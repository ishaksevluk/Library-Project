<?php
session_start();
require_once 'repositories/database_repository.php';

$bookId = $_SESSION['book_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;

try {
   
    $conn = getConnection();

    $sql = "BEGIN IFSAPP.F8lib_Borrow_Records_API.Add_Borrow_Records(:book_id, :user_id); END;";
    $stid = oci_parse($conn, $sql);

  
    oci_bind_by_name($stid, ':book_id', $bookId);
    oci_bind_by_name($stid, ':user_id', $userId);
    

    if (oci_execute($stid)) {
        // Bildirim ve takip bilgilerini session'a yaz
        try {
            $book = getBookById((int)$bookId);
            $bookName = is_array($book) ? (string)($book['BOOK_NAME'] ?? $book['Book_Name'] ?? 'Kitap') : 'Kitap';
        } catch (Throwable $e) {
            $bookName = 'Kitap';
        }

        // Ödünç alma bildirimi
        if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }
        $_SESSION['notifications'][] = [
            'type' => 'borrow_success',
            'book_id' => (int)$bookId,
            'book_name' => $bookName,
            'time' => time()
        ];

        // İade tarihi ve hatırlatma planı (15 gün), her 5 günde bir hatırlatma
        if (!isset($_SESSION['borrow_tracking']) || !is_array($_SESSION['borrow_tracking'])) {
            $_SESSION['borrow_tracking'] = [];
        }
        $_SESSION['borrow_tracking'][(int)$bookId] = [
            'borrowed_at' => time(),
            'due_at' => time() + 15 * 86400,
            'reminded_days' => [] // 5,10,15 gün için işaretlenecek
        ];

        echo "✅ Başarıyla ödünç alındı!";
    } else {
        $err = oci_error($stid);
        echo "❌ Hata: " . $err['message'];
    }

    
    oci_free_statement($stid);
    oci_close($conn);

} catch (Exception $e) {
    echo "❌ Bağlantı veya işlem sırasında hata oluştu: " . $e->getMessage();
}
?>