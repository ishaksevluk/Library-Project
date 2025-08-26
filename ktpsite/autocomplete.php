<?php
header('Content-Type: application/json');

$host = "poziq7y-dev2-db.build.ifs.cloud";
$dbname = "alepdb";
$username = "IFSAPP";
$password = "83Gv7Uoou5sfXUpNvOjv0V0G2xVYa6";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($term === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT book_name, author_name FROM books 
        WHERE book_name LIKE :term 
           OR author_name LIKE :term 
           OR description LIKE :term
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute(['term' => "%$term%"]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);