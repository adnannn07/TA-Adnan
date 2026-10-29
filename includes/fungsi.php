<?php
// includes/fungsi.php
// Kumpulan fungsi bantu yang dipakai di banyak halaman

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape teks sebelum ditampilkan ke HTML, mencegah XSS.
 */
function h($string) {
    return htmlspecialchars((string) ($string ?? ''), ENT_QUOTES, 'UTF-8');
}

function getAbbrText($text, $length = 2) {
    $text = trim(preg_replace('/[^A-Za-z0-9 ]+/', ' ', (string) $text));
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $abbr = '';
    foreach ($words as $word) {
        if ($word === '') {
            continue;
        }
        if (preg_match('/^[0-9]+/', $word, $matches)) {
            $abbr .= $matches[0];
        } else {
            $abbr .= strtoupper($word[0]);
        }
        if (strlen($abbr) >= $length) {
            break;
        }
    }
    return strtoupper(substr($abbr, 0, $length));
}

function toBase36Char($value) {
    $value = max(0, min(35, (int) $value));
    if ($value < 10) {
        return (string) $value;
    }
    return chr(55 + $value); // 10 -> A, 11 -> B, ...
}

function getSubKategoriPrefix($conn, $subKategoriId) {
    $stmt = $conn->prepare("SELECT k.nama AS kategori, s.nama AS subkategori FROM sub_kategori s JOIN kategori k ON s.kategori_id=k.id WHERE s.id = ?");
    $stmt->bind_param('i', $subKategoriId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return 'ALT';
    }

    $catAbbr = getAbbrText($row['kategori'], 2);
    $subAbbr = getAbbrText($row['subkategori'], 2);
    return strtoupper(substr($catAbbr . $subAbbr, 0, 4));
}

function encodeSubKriteriaSignature(array $subValues) {
    $highCount = 0;
    $midCount = 0;
    foreach ($subValues as $value) {
        if ((float) $value === 0.50) {
            $highCount++;
        } elseif ((float) $value === 0.35) {
            $midCount++;
        }
    }
    return toBase36Char($highCount) . toBase36Char($midCount);
}

function generateKodeAlternatif($conn, $subKategoriId, array $subValues) {
    $prefix = getSubKategoriPrefix($conn, $subKategoriId);
    $signature = encodeSubKriteriaSignature($subValues);
    $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', $prefix . $signature));
    if ($base === '') {
        $base = 'ALT';
    }
    $base = substr($base, 0, 8);

    $likePattern = $base . '%';
    $stmt = $conn->prepare("SELECT kode FROM alternatif WHERE kode LIKE ? ORDER BY kode DESC");
    $stmt->bind_param('s', $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();

    $highestSeq = 0;
    while ($row = $result->fetch_assoc()) {
        if (preg_match('/(\d{2})$/', $row['kode'], $matches)) {
            $seq = (int) $matches[1];
            $highestSeq = max($highestSeq, $seq);
        }
    }
    $stmt->close();

    return $base . str_pad($highestSeq + 1, 2, '0', STR_PAD_LEFT);
}

/**
 * Format tanggal ke dalam bahasa Indonesia lengkap dengan nama hari.
 */
function formatTanggalIndonesia($tanggal = null) {
    $ts = $tanggal ? strtotime($tanggal) : time();
    $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

    return $hari[date('l', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan[date('F', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Pastikan hanya admin yang sudah login yang bisa mengakses halaman.
 * Jika belum login, redirect ke login.php dan hentikan eksekusi (exit),
 * supaya konten halaman tidak ikut ter-render sebelum redirect terjadi.
 */
function requireAdmin() {
    if (!isset($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}
