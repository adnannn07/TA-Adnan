<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/header.php';
?>
<div class="container mt-4">
    <div class="hero-panel">
        <h2>Selamat Datang, Admin</h2>
        <p>Kelola data paket rakitan komputer, kriteria WP, dan cetak laporan dari sini.</p>
    </div>
    <div class="row mt-2">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Alternatif</h5>
                    <p><?= $conn->query("SELECT COUNT(*) as total FROM alternatif")->fetch_assoc()['total'] ?> paket</p>
                    <a href="alternatif.php" class="btn btn-light btn-sm">Kelola</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Kategori</h5>
                    <p><?= $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'] ?> utama</p>
                    <a href="kategori.php" class="btn btn-light btn-sm">Kelola</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Kriteria</h5>
                    <p>9 kriteria</p>
                    <a href="kriteria.php" class="btn btn-light btn-sm">Lihat</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Laporan</h5>
                    <p>Cetak data</p>
                    <a href="laporan.php" class="btn btn-light btn-sm">Cetak</a>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="logout.php" class="btn btn-danger">Keluar</a>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>