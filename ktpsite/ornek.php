<?php
require_once 'repositories/database_repository.php';

$bookId = '1';
$userId = '3';   

try {
   
    $conn = getConnection();

    // PL/SQL blok ile prosedürü çağır
    $sql = "BEGIN IFSAPP.F8lib_Reservations_API.Add_Reservations(:book_id, :user_id); END;";
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