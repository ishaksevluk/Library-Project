<?php 
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
    die('🔌 Oracle bağlantı hatası: ' . $error['message']);
} else {
    echo ("başarılı");
}
?>