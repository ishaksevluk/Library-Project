<?php
session_start();
require_once 'repositories/database_repository.php';

$reservationId = '1';


try {
   
    $conn = getConnection();

    $sql = "DELETE FROM F8LIB_RESERVATIONS_TAB WHERE RESERVATION_ID = :reservation_id";
    $stid = oci_parse($conn, $sql);

  
    oci_bind_by_name($stid, ':reservation_id', $reservationId);
    

    if (oci_execute($stid)) {
        echo "✅ Başarıyla rezervasyon silindi!";
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