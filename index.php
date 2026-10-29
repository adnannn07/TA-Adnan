<?php
require_once 'config/database.php';
require_once 'includes/fungsi.php';
require_once 'includes/wp_calculation.php';
require_once 'includes/header.php';

// Ambil 1 rekomendasi teratas dari beberapa kategori sebagai "Produk Unggulan"
$kriteriaAll = getKriteria($conn);
$kategoriUnggulan = $conn->query("SELECT * FROM kategori ORDER BY id LIMIT 6");
$unggulan = [];
while ($kat = $kategoriUnggulan->fetch_assoc()) {
    $stmt = $conn->prepare(
        "SELECT a.* FROM alternatif a JOIN sub_kategori s ON a.sub_kategori_id = s.id WHERE s.kategori_id = ?"
    );
    $stmt->bind_param('i', $kat['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $alts = [];
    while ($row = $res->fetch_assoc()) $alts[] = $row;
    $stmt->close();
    if (count($alts) > 0) {
        $hasil = hitungWP($alts, $kriteriaAll);
        $unggulan[] = ['kategori' => $kat['nama'], 'produk' => $hasil[0]];
    }
}
?>
<div class="container mt-4">
    <div class="hero-panel">
        <h2>Selamat Datang!</h2>
        <p>Vira Jaya Computer &mdash; Sistem Pendukung Keputusan pemilihan paket rakitan komputer dengan metode Weighted Product</p>
    </div>

    <h4 class="mb-3">Paket Rakitan Komputer</h4>
    <div class="row g-3">
        <?php foreach ($unggulan as $u): $p = $u['produk']; ?>
        <div class="col-md-4">
            <div class="card h-100 product-card">
                <div class="<?= $p['foto'] ? '' : 'card-photo-lg' ?>">
                    <?php if ($p['foto']): ?>
                    <img src="<?= h($p['foto']) ?>" class="card-photo-img">
                    <?php else: ?>X<?php endif; ?>
                </div>
                <div class="card-body">
                    <span class="badge bg-secondary mb-2"><?= h($u['kategori']) ?></span>
                    <h6 class="card-title mb-1"><?= h($p['nama']) ?></h6>
                    <p class="text-muted small mb-2"><?= h($p['detail_processor']) ?></p>
                    <p class="fw-bold mb-3">Rp <?= number_format($p['harga_total'],0,',','.') ?></p>
                    <a href="detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-primary btn-sm w-100">Lihat Detail</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="daftar_paket.php" class="btn btn-outline-secondary">Lihat Semua Paket &rarr;</a>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
