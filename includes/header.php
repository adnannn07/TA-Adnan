<?php
require_once __DIR__ . '/fungsi.php';
require_once __DIR__ . '/upload_helper.php';
$base = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
$isAdminArea = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$currentFile = basename($_SERVER['PHP_SELF']);
$logoPath = getLogoPath();
function navActive($file, $current) { return $file === $current ? 'active-tab' : ''; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vira Jaya Computer — SPK Paket Rakitan Komputer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="site-header-top">
            <div class="brand-mark"><img src="<?= $base ?>assets/img/logovj.png" alt="Logo"></div>
            <div>
                <div class="brand-name">VIRA JAYA COMPUTER</div>
                <div class="brand-sub"><?= (isset($_SESSION['admin']) && $isAdminArea) ? 'Admin Panel' : 'Customer Portal' ?></div>
            </div>
            <?php if ($isAdminArea && isset($_SESSION['admin']) && $currentFile !== 'index.php'): ?>
            <a href="<?= $base ?>admin/logout.php" class="btn btn-sm btn-outline-light ms-auto">Keluar</a>
            <?php endif; ?>
        </div>
        <nav class="site-tabs">
            <?php if (isset($_SESSION['admin']) && $isAdminArea): ?>
                <a class="site-tab <?= navActive('index.php',$currentFile) ?>" href="<?= $base ?>admin/index.php">Beranda</a>
                <a class="site-tab <?= navActive('alternatif.php',$currentFile) ?>" href="<?= $base ?>admin/alternatif.php">Kelola Paket</a>
                <a class="site-tab <?= navActive('kategori.php',$currentFile) ?>" href="<?= $base ?>admin/kategori.php">Kelola Kategori</a>
                <a class="site-tab <?= navActive('kriteria.php',$currentFile) ?>" href="<?= $base ?>admin/kriteria.php">Kriteria</a>
                <a class="site-tab <?= navActive('laporan.php',$currentFile) ?>" href="<?= $base ?>admin/laporan.php">Laporan</a>
              
            <?php else: ?>
                <a class="site-tab <?= navActive('index.php',$currentFile) ?>" href="<?= $base ?>index.php">Beranda</a>
                <a class="site-tab <?= navActive('daftar_paket.php',$currentFile) ?>" href="<?= $base ?>daftar_paket.php">Daftar Paket</a>
                <a class="site-tab <?= navActive('rekomendasi.php',$currentFile) ?>" href="<?= $base ?>rekomendasi.php">Rekomendasi</a>
                <a class="site-tab ms-auto" href="<?= $base ?>admin/index.php">Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<div class="container mt-3">