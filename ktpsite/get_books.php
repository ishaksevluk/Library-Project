<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
require_once 'repositories/database_repository.php';

if (!isset($_GET['category'])) {
    echo json_encode([]);
    exit;
}

$categoryId = $_GET['category'];

// Repo oluştur ve kitapları çek
$repo = new OracleBookRepository();
$books = $repo->getByCategoryId($categoryId);

// JSON olarak gönder
header('Content-Type: application/json; charset=utf-8');
echo json_encode($books);
?>
</body>
</html>