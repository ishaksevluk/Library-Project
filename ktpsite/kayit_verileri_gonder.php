<?php

require_once 'repositories/database_repository.php';
$kayitUsername = 'Kerem';
$kayitEmail = 'kerem@gmail.com';
$kayitPassword = '12345';
$kayitActivation = 0;



try {
   
    $conn = getConnection();

    
    oci_bind_by_name($stid, ':email_txt', $kayitEmail);
    oci_bind_by_name($stid, ':password_txt', $kayitPassword);
    oci_bind_by_name($stid, ':user_activation', $kayitActivation);

    $sql = "BEGIN IFSAPP.F8lib_Users_API.Add_Users(:name_txt, :email_txt, :password_txt, :user_activation); END;";
    $stid = oci_parse($conn, $sql);

  
    



    if (oci_execute($stid)) {
        echo '<div class="kayit-bildirim evt">✅ Kayıt başarıyla gönderildi!</div>';
    } else {
        $err = oci_error($stid);
        echo '<div class=kayit-bildirim hyr">❌ Hata: ' . $err['message'] . '</div>';
    }

    
    oci_free_statement($stid);
    oci_close($conn);

} catch (Exception $e) {
    echo "❌ Bağlantı veya işlem sırasında hata oluştu: " . $e->getMessage();
}
?>
<style>
    .kayit-bildirim {
        opacity: 0.5;
        background-color: #00174A;
        width: 100%;
        width: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }

    .kayit-bildirim evt{
        color: rgba(52, 197, 39, 1);
    }

    .kayit-bildirim hyr{
        color: rgba(202, 26, 26, 1);
    }
</style>