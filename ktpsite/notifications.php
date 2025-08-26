<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'repositories/database_repository.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$now = time();

$items = [];

// 1) Session tabanlı yeni kitap eklendi bildirimleri (örnek mantık)
// Uygulamada gerçek tetikleyici bir insert sonrası session'a yazılmalıdır.
if (isset($_SESSION['new_books']) && is_array($_SESSION['new_books'])) {
    foreach ($_SESSION['new_books'] as $book) {
        $items[] = [
            'type' => 'new_book',
            'icon' => '📖',
            'title' => 'Yeni Kitap Eklendi',
            'message' => '"' . (string)($book['name'] ?? 'Kitap') . '" kitaplığa eklendi.',
            'time' => (int)($book['time'] ?? $now)
        ];
    }
    // Tek seferlik gösterim için temizlemek isterseniz:
    // unset($_SESSION['new_books']);
}

// 2) Ödünç alma bildirimi (odunc_al.php yazar)
if (isset($_SESSION['notifications']) && is_array($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $n) {
        if (($n['type'] ?? '') === 'borrow_success') {
            $items[] = [
                'type' => 'borrow_success',
                'icon' => '✅',
                'title' => 'Ödünç Alındı',
                'message' => '"' . (string)($n['book_name'] ?? 'Kitap') . '" başarıyla ödünç alındı.',
                'time' => (int)($n['time'] ?? $now)
            ];
        }
    }
}

// 3) İade süresi yaklaşanlar: her 5 günde bir hatırlatma (5,10,15)
if (isset($_SESSION['borrow_tracking']) && is_array($_SESSION['borrow_tracking'])) {
    foreach ($_SESSION['borrow_tracking'] as $bookId => $track) {
        $borrowedAt = (int)($track['borrowed_at'] ?? $now);
        $dueAt = (int)($track['due_at'] ?? ($borrowedAt + 15 * 86400));
        $daysPassed = floor(($now - $borrowedAt) / 86400);
        $remindedDays = isset($track['reminded_days']) && is_array($track['reminded_days']) ? $track['reminded_days'] : [];

        $milestones = [5, 10, 15];
        foreach ($milestones as $d) {
            if ($daysPassed >= $d && !in_array($d, $remindedDays, true)) {
                // Kitap adını çekmeye çalış
                $book = getBookById((int)$bookId);
                $bookName = is_array($book) ? (string)($book['BOOK_NAME'] ?? $book['Book_Name'] ?? 'Kitap') : 'Kitap';

                $items[] = [
                    'type' => 'due_reminder',
                    'icon' => '⏰',
                    'title' => 'Kitap İade Süresi Yaklaşıyor',
                    'message' => '"' . $bookName . '" iade tarihi yaklaşıyor. Kalan gün: ' . max(0, ceil(($dueAt - $now)/86400)),
                    'time' => $now
                ];

                // Tekrar bildirmemek için işaretle
                $_SESSION['borrow_tracking'][(int)$bookId]['reminded_days'][] = $d;
            }
        }
    }
}

// Zaman sırasına göre sırala (yeni → eski)
usort($items, function($a, $b){ return ($b['time'] ?? 0) <=> ($a['time'] ?? 0); });

echo json_encode(['ok' => 1, 'notifications' => $items], JSON_UNESCAPED_UNICODE);
?>