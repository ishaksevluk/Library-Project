<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kayıt Sayfası</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .kayit-bildirim { padding:10px; border-radius:6px; margin-top:10px; display:none; }
    .evt { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .hyr { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
  </style>
</head>
<body>
    <div class="register-page">
        <div class="form-container">
            <img src="images/F8-logo.png" alt="Logo" class="logo-img">
            <h2>Kayıt Ol</h2>
            <form id="register-form" method="post" action="">
                <input type="text" name="name" placeholder="Kullanıcı Adı" required>
                <input type="email" name="email" placeholder="E-posta" required>
                <input type="password" name="password" placeholder="Şifre" required>
                <button type="submit" name="kayitSubmit">Kayıt Ol</button>
            </form>

           
            <div id="kayit-basari" class="kayit-bildirim evt">✅ Kayıt başarıyla gönderildi!</div>
            <div id="kayit-hata" class="kayit-bildirim hyr">❌ Hata oluştu!</div>
        </div>
    </div>

<?php
require_once 'repositories/database_repository.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kayitSubmit'])) {
    $kayitUsername = $_POST['name'] ?? '';
    $kayitEmail = $_POST['email'] ?? '';
    $kayitPassword = $_POST['password'] ?? '';
    $kayitActivation = 0;

    

    try {
        $conn = getConnection();

        $sql = "BEGIN IFSAPP.F8lib_Users_API.Add_Users(:name_txt, :email_txt, :password_txt, :user_activation); END;";
        $stid = oci_parse($conn, $sql);

        oci_bind_by_name($stid, ':name_txt', $kayitUsername);
        oci_bind_by_name($stid, ':email_txt', $kayitEmail);
        oci_bind_by_name($stid, ':password_txt', $kayitPassword);
        oci_bind_by_name($stid, ':user_activation', $kayitActivation, 500, SQLT_CHR);

        if (oci_execute($stid)) {
            echo "<script>
                document.getElementById('kayit-basari').style.display = 'block';
                document.getElementById('kayit-hata').style.display = 'none';
            </script>";
        } else {
            $err = oci_error($stid);
            $hataMesaji = $err['message'];
            echo "<script>
                document.getElementById('kayit-hata').textContent = '❌ Hata: ' + " . json_encode($hataMesaji) . ";
                document.getElementById('kayit-hata').style.display = 'block';
                document.getElementById('kayit-basari').style.display = 'none';
            </script>";
        }

        oci_free_statement($stid);
        oci_close($conn);

    } catch (Exception $e) {
        $mesaj = $e->getMessage();
        echo "<script>
            document.getElementById('kayit-hata').textContent = '❌ Bağlantı/Hata: ' + " . json_encode($mesaj) . ";
            document.getElementById('kayit-hata').style.display = 'block';
            document.getElementById('kayit-basari').style.display = 'none';
        </script>";
    }
}
?>
</body>
</html>