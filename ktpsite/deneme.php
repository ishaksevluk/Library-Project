<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Php Deneme</title>
</head>
<body>
    <?php
require_once 'repositories/database_repository.php';

$bookRepository = new OracleBookRepository();
$books = $bookRepository->getAll();
?>
<h1>📚 Kitaplar</h1>

    <?php foreach ($books as $book): ?>
    <div class="book-card">
        <h2><?= htmlspecialchars($book['BOOK_NAME']) ?></h2>
        <p><strong>Yazar ID:</strong> <?= htmlspecialchars($book['AUTHOR_ID']) ?></p>
        <p><strong>Yayınevi:</strong> <?= htmlspecialchars($book['PUBLISHER']) ?></p>
        <p><strong>ISBN:</strong> <?= htmlspecialchars($book['ISBN']) ?></p>
        <p><strong>Yıl:</strong> <?= htmlspecialchars($book['PUBLISH_YEAR']) ?></p>
        <p><strong>Özet:</strong> <?= nl2br(htmlspecialchars($book['BOOK_SUMMARY'])) ?></p>
        
        <?php if (!empty($book['BOOK_IMAGE'])): ?>
            <img src="<?= htmlspecialchars($book['BOOK_IMAGE']) ?>" alt="Kitap Resmi" width="150">
        <?php else: ?>
            <img src="placeholder.jpg" alt="Resim Yok" width="150">
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</body>
</html>