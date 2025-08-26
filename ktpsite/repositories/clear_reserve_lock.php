<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'repositories/database_repository.php';

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($bookId <= 0) {
    echo json_encode(['ok' => 0]);
    exit;
}

clearReserveLockForBook($bookId);
echo json_encode(['ok' => 1]);
?>