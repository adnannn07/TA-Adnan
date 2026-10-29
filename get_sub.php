<?php
// get_sub.php
// Endpoint AJAX: mengembalikan daftar sub kategori (JSON) berdasarkan kategori_id.
// Dipanggil dari script di index.php saat dropdown "Kategori Utama" berubah.

require_once 'config/database.php';

header('Content-Type: application/json');

$kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;

$data = [];
if ($kategori_id > 0) {
    $stmt = $conn->prepare("SELECT id, nama FROM sub_kategori WHERE kategori_id = ? ORDER BY nama");
    $stmt->bind_param('i', $kategori_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

echo json_encode($data);
