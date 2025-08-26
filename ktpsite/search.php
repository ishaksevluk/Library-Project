<?php
// search.php - Türkçe karakter uyumlu arama
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'repositories/database_repository.php';

$type = $_GET['type'] ?? 'all'; // all | author_books | publisher_books | book_single
$term = $_GET['term'] ?? '';
$author_id = $_GET['author_id'] ?? null;
$publisher = $_GET['publisher'] ?? null;
$book_id = $_GET['id'] ?? null;

$term = trim($term);
// Türkçe karakter uyumlu büyük harfe çevirme
$lcLike = '%' . mb_strtoupper($term, 'UTF-8') . '%';

$response = [
    'kitaplar' => [],
    'yazarlar' => [],
    'yayinevleri' => []
];

try {
    $conn = getConnection();
} catch (Exception $e) {
    echo json_encode(['error' => 'DB bağlantı hatası: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // -----------------------
    // 1) Genel arama (all)
    // -----------------------
    if ($type === 'all') {
        if ($term !== '') {
            // Kitaplar: book name OR author name OR publisher
            $sqlBooks = "
                SELECT DISTINCT b.BOOK_ID, b.BOOK_NAME, b.BOOK_IMAGE, b.AUTHOR_ID, b.PUBLISHER, b.CATEGORY_ID,
                       COALESCE(c.DESCRIPTION, '') AS CATEGORY_NAME,
                       COALESCE(a.AUTHOR_NAME, '') AS AUTHOR_NAME
                FROM F8LIB_BOOKS b
                LEFT JOIN F8LIB_AUTHORS a ON b.AUTHOR_ID = a.AUTHOR_ID
                LEFT JOIN F8LIB_CATEGORIES c ON b.CATEGORY_ID = c.CATEGORY_ID
                WHERE UPPER(b.BOOK_NAME) LIKE :term
                   OR UPPER(a.AUTHOR_NAME) LIKE :term
                   OR UPPER(b.PUBLISHER) LIKE :term
                ORDER BY b.BOOK_NAME
            ";
            $stid = oci_parse($conn, $sqlBooks);
            oci_bind_by_name($stid, ':term', $lcLike);
            oci_execute($stid);
            while ($row = oci_fetch_assoc($stid)) {
                $response['kitaplar'][] = $row;
            }
            oci_free_statement($stid);
        }

        // Yazarlar
        if ($term !== '') {
            $sqlAuthors = "
                SELECT DISTINCT AUTHOR_ID, AUTHOR_NAME
                FROM F8LIB_AUTHORS
                WHERE UPPER(AUTHOR_NAME) LIKE :term
                ORDER BY AUTHOR_NAME
            ";
            $stid = oci_parse($conn, $sqlAuthors);
            oci_bind_by_name($stid, ':term', $lcLike);
            oci_execute($stid);
            while ($row = oci_fetch_assoc($stid)) {
                $response['yazarlar'][] = $row;
            }
            oci_free_statement($stid);
        }

        // Yayınevleri
        if ($term !== '') {
            $sqlPubs = "
                SELECT DISTINCT PUBLISHER
                FROM F8LIB_BOOKS
                WHERE UPPER(PUBLISHER) LIKE :term
                ORDER BY PUBLISHER
            ";
            $stid = oci_parse($conn, $sqlPubs);
            oci_bind_by_name($stid, ':term', $lcLike);
            oci_execute($stid);
            while ($row = oci_fetch_assoc($stid)) {
                $response['yayinevleri'][] = $row;
            }
            oci_free_statement($stid);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -----------------------
    // 2) author_books
    // -----------------------
    if ($type === 'author_books') {
        if ($author_id) {
            $sql = "
                SELECT b.BOOK_ID, b.BOOK_NAME, b.BOOK_IMAGE, b.AUTHOR_ID, b.PUBLISHER, b.CATEGORY_ID,
                       COALESCE(c.DESCRIPTION, '') AS CATEGORY_NAME
                FROM F8LIB_BOOKS b
                LEFT JOIN F8LIB_CATEGORIES c ON b.CATEGORY_ID = c.CATEGORY_ID
                WHERE b.AUTHOR_ID = :author_id
                ORDER BY b.BOOK_NAME
            ";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ':author_id', $author_id);
            oci_execute($stid);
            $books = [];
            while ($row = oci_fetch_assoc($stid)) {
                $books[] = $row;
            }
            oci_free_statement($stid);

            // ID ile sonuç yoksa isimle dene
            if (empty($books)) {
                $sqlA = "SELECT AUTHOR_NAME FROM F8LIB_AUTHORS WHERE AUTHOR_ID = :author_id";
                $stidA = oci_parse($conn, $sqlA);
                oci_bind_by_name($stidA, ':author_id', $author_id);
                oci_execute($stidA);
                $r = oci_fetch_assoc($stidA);
                oci_free_statement($stidA);
                if ($r && !empty($r['AUTHOR_NAME'])) {
                    $authorName = $r['AUTHOR_NAME'];
                    $sql2 = "
                        SELECT b.BOOK_ID, b.BOOK_NAME, b.BOOK_IMAGE, b.AUTHOR_ID, b.PUBLISHER, b.CATEGORY_ID,
                               COALESCE(c.DESCRIPTION, '') AS CATEGORY_NAME
                        FROM F8LIB_BOOKS b
                        LEFT JOIN F8LIB_AUTHORS a ON b.AUTHOR_ID = a.AUTHOR_ID
                        LEFT JOIN F8LIB_CATEGORIES c ON b.CATEGORY_ID = c.CATEGORY_ID
                        WHERE UPPER(a.AUTHOR_NAME) = UPPER(:authorName)
                        ORDER BY b.BOOK_NAME
                    ";
                    $stid2 = oci_parse($conn, $sql2);
                    oci_bind_by_name($stid2, ':authorName', $authorName);
                    oci_execute($stid2);
                    while ($row2 = oci_fetch_assoc($stid2)) {
                        $books[] = $row2;
                    }
                    oci_free_statement($stid2);
                }
            }

            echo json_encode($books, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // -----------------------
    // 3) publisher_books
    // -----------------------
    if ($type === 'publisher_books') {
        if (!$publisher) {
            echo json_encode(['error' => 'publisher parametresi gerekli.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $sql = "
            SELECT b.BOOK_ID, b.BOOK_NAME, b.BOOK_IMAGE, b.AUTHOR_ID, b.PUBLISHER, b.CATEGORY_ID,
                   COALESCE(c.DESCRIPTION, '') AS CATEGORY_NAME
            FROM F8LIB_BOOKS b
            LEFT JOIN F8LIB_CATEGORIES c ON b.CATEGORY_ID = c.CATEGORY_ID
            WHERE UPPER(b.PUBLISHER) = UPPER(:publisher)
            ORDER BY b.BOOK_NAME
        ";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':publisher', $publisher);
        oci_execute($stid);
        $books = [];
        while ($row = oci_fetch_assoc($stid)) {
            $books[] = $row;
        }
        oci_free_statement($stid);
        echo json_encode($books, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // -----------------------
    // 4) book_single
    // -----------------------
    if ($type === 'book_single') {
        if (!$book_id) {
            echo json_encode(['error' => 'id parametresi gerekli.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $sql = "
            SELECT b.BOOK_ID, b.BOOK_NAME, b.BOOK_IMAGE, b.AUTHOR_ID, b.PUBLISHER, b.CATEGORY_ID,
                   COALESCE(c.DESCRIPTION, '') AS CATEGORY_NAME
            FROM F8LIB_BOOKS b
            LEFT JOIN F8LIB_CATEGORIES c ON b.CATEGORY_ID = c.CATEGORY_ID
            WHERE b.BOOK_ID = :book_id
        ";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':book_id', $book_id);
        oci_execute($stid);
        $row = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        if ($row === false) echo json_encode([], JSON_UNESCAPED_UNICODE);
        else echo json_encode($row, JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['error' => 'Bilinmeyen type veya eksik parametre.'], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    $err = oci_error() ?: [];
    $msg = $e->getMessage() . (isset($err['message']) ? ' | OCI: ' . $err['message'] : '');
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    if (isset($stid) && $stid) @oci_free_statement($stid);
    if (isset($conn) && $conn) @oci_close($conn);
}
?>