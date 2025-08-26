<?php
session_start();
require_once 'repositories/database_repository.php';

$bookId = $_SESSION['book_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;

try {
   
    $conn = getConnection();

    $sql = "BEGIN IFSAPP.F8lib_Reservations_API.Add_Reservations (:book_id, :user_id); END;";
    $stid = oci_parse($conn, $sql);

  
    oci_bind_by_name($stid, ':book_id', $bookId);
    oci_bind_by_name($stid, ':user_id', $userId);
    

    if (oci_execute($stid)) {
    echo "✅ Rezervasyon başarıyla eklendi!";
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