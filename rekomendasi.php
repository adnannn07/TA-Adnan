<?php
require_once 'config/database.php';
require_once 'includes/fungsi.php';
require_once 'includes/wp_calculation.php';
require_once 'includes/header.php';

$kriteriaAll = getKriteria($conn);
$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama");
$kategoriRows = [];
while ($k = $kategoriList->fetch_assoc()) $kategoriRows[] = $k;
?>
<div class="container mt-4">
    <h3 class="mb-3">Rekomendasi Paket Rakitan Komputer</h3>
    <p class="text-muted">3 paket terbaik hasil perhitungan Weighted Product untuk setiap kategori.</p>

    <!-- Pencarian spesifik per sub-kategori -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Cari Rekomendasi Spesifik</h6>
            <form action="ranking.php" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Kategori Utama</label>
                    <select name="kategori" id="kategori" class="form-select" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php foreach ($kategoriRows as $k): ?>
                        <option value="<?= (int) $k['id'] ?>"><?= h($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Sub Kategori</label>
                    <select name="sub" id="sub" class="form-select" required>
                        <option value="">-- Pilih kategori terlebih dahulu --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <?php foreach ($kategoriRows as $kat): ?>
        <?php
        $stmt = $conn->prepare("SELECT a.* FROM alternatif a JOIN sub_kategori s ON a.sub_kategori_id=s.id WHERE s.kategori_id = ?");
        $stmt->bind_param('i', $kat['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $alts = [];
        while ($row = $res->fetch_assoc()) $alts[] = $row;
        $stmt->close();
        if (count($alts) === 0) continue;
        $hasil = array_slice(hitungWP($alts, $kriteriaAll), 0, 3);
        ?>
    <div class="card mb-3">
        <div class="card-header"><strong>Rekomendasi <?= h($kat['nama']) ?></strong></div>
        <ul class="list-group list-group-flush">
            <?php foreach ($hasil as $i => $p): ?>
            <li class="list-group-item d-flex align-items-center gap-3">
                <span class="rank-badge <?= $i==0 ? 'gold' : '' ?>"><?= $i+1 ?></span>
                <?php if ($p['foto']): ?>
                <img src="<?= h($p['foto']) ?>" class="thumb-list" style="width:44px;height:44px;">
                <?php else: ?>
                <div class="thumb-list-placeholder" style="width:44px;height:44px;">—</div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= h($p['nama']) ?></div>
                    <div class="text-muted small"><?= h($p['detail_processor']) ?> &middot; <?= h($p['detail_vga']) ?></div>
                    <div class="mt-1">
                        <span class="badge bg-light text-dark">Peringkat #<?= $i + 1 ?></span>
                        <span class="badge bg-success-subtle text-success">Vektor V: <?= number_format($p['nilai_persen'], 4, '.', '') ?>%</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">Rp <?= number_format($p['harga_total'],0,',','.') ?></div>
                </div>
                <a href="detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
