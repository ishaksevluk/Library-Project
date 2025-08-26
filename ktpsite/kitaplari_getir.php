<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once 'repositories/database_repository.php';
header('Content-Type: application/json');

$bookRepo = new OracleBookRepository();

// “Tümü” kategorisi
if (isset($_GET['kategori']) && $_GET['kategori'] === 'all') {
    $books = $bookRepo->getAll();
    echo json_encode($books);
    exit;
}

// Normal kategori ID
if (!isset($_GET['kategori'])) {
    echo json_encode(['error' => 'Kategori ID gelmedi']);
    exit;
}

$categoryId = $_GET['kategori'];
$books = $bookRepo->getByCategoryId($categoryId);

echo json_encode($books);
?>