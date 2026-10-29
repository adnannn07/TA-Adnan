<?php
/**
 * includes/upload_helper.php
 * Helper sederhana untuk menangani upload gambar (foto produk & logo).
 */

/**
 * Proses upload file dari $_FILES[$fieldName] ke folder tujuan.
 * Mengembalikan path relatif (terhadap root project) jika berhasil,
 * atau null jika tidak ada file yang diupload (mis. saat edit tanpa ganti foto),
 * atau melempar Exception dengan pesan jika file tidak valid.
 *
 * @param string $fieldName nama input file, mis. 'foto'
 * @param string $destDirFromRoot folder tujuan relatif dari root project, mis. 'uploads/produk'
 * @param string $prefix prefix nama file, mis. kode produk
 */
function handleImageUpload($fieldName, $destDirFromRoot, $prefix = 'img') {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada file baru diupload
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload gagal (kode error: ' . $_FILES[$fieldName]['error'] . ').');
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSizeBytes = 2 * 1024 * 1024; // 2MB

    $originalName = $_FILES[$fieldName]['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.');
    }
    if ($_FILES[$fieldName]['size'] > $maxSizeBytes) {
        throw new Exception('Ukuran file maksimal 2MB.');
    }

    // Folder tujuan absolut di server, relatif terhadap file ini (includes/) naik satu level ke root
    $destDirAbsolute = __DIR__ . '/../' . $destDirFromRoot;
    if (!is_dir($destDirAbsolute)) {
        mkdir($destDirAbsolute, 0755, true);
    }

    $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix);
    $filename = $safePrefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destPath = $destDirAbsolute . '/' . $filename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destPath)) {
        throw new Exception('Gagal menyimpan file ke server. Periksa izin folder ' . $destDirFromRoot . '.');
    }

    return $destDirFromRoot . '/' . $filename; // path relatif dari root project, disimpan ke DB
}

/**
 * Hapus file lama dari server (dipakai saat foto diganti dengan yang baru).
 * $pathFromRoot: path relatif dari root project, seperti yang tersimpan di DB.
 */
function deleteOldImage($pathFromRoot) {
    if (!$pathFromRoot) return;
    $absolute = __DIR__ . '/../' . $pathFromRoot;
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

/**
 * Cari file logo yang sudah diupload di assets/img/logo.* (ekstensi apa saja
 * yang didukung). Mengembalikan path relatif dari root project, atau null
 * jika belum ada logo yang diupload sama sekali.
 */
function getLogoPath() {
    $candidates = [
        'assets/img/logo.png',
        'assets/img/logo.jpg',
        'assets/img/logo.jpeg',
        'assets/img/logo.webp',
        'assets/img/logovj.png',
        'assets/img/logovj.jpg',
        'assets/img/logovj.jpeg',
        'assets/img/logovj.webp',
    ];

    foreach ($candidates as $rel) {
        if (is_file(__DIR__ . '/../' . $rel)) {
            return $rel;
        }
    }

    return null;
}
