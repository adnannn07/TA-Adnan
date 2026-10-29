<?php
require_once 'config/database.php';
require_once 'includes/wp_calculation.php';
require_once 'includes/header.php';

$sub_id = isset($_GET['sub']) ? intval($_GET['sub']) : 0;
if (!$sub_id) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Sub kategori tidak dipilih.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Ambil data alternatif berdasarkan sub_kategori
$altQuery = $conn->query("SELECT * FROM alternatif WHERE sub_kategori_id = $sub_id");
$alternatif = [];
while ($row = $altQuery->fetch_assoc()) {
    $alternatif[] = $row;
}

if (count($alternatif) == 0) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Belum ada paket untuk sub kategori ini.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Ambil kriteria
$kriteria = getKriteria($conn);

// Hitung WP
$hasil = hitungWP($alternatif, $kriteria);

// Ambil nama sub kategori
$sub = $conn->query("SELECT nama FROM sub_kategori WHERE id=$sub_id")->fetch_assoc();
?>

<div class="container mt-4">
    <div class="hero-panel">
        <h2>Hasil Rekomendasi: <?= h($sub['nama']) ?></h2>
        <p>Diurutkan berdasarkan nilai preferensi (V) tertinggi — metode Weighted Product.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-hover" id="rankingTable">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Kode</th>
                    <th>Nama Paket</th>
                    <th>Nilai S</th>
                    <th>Nilai V</th>
                    <th class="text-end">Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank=1; foreach ($hasil as $h): ?>
                <tr>
                    <td><span class="rank-badge <?= $rank<=3 ? 'gold' : '' ?>"><?= $rank++ ?></span></td>
                    <td><span class="badge-kode"><?= h($h['kode']) ?></span></td>
                    <td class="fw-semibold"><?= h($h['nama']) ?></td>
                    <td><?= number_format($h['nilai_S'], 6) ?></td>
                    <td><?= number_format($h['nilai_persen'], 4, '.', '') ?>%</td>
                    <td class="text-end">Rp <?= number_format($h['harga_total'], 0, ',', '.') ?></td>
                    <td><a href="detail.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tombol cetak / laporan -->
    <div class="mt-3 no-print">
        <a href="laporan_penggunan.php?sub=<?= $sub_id ?>" class="btn btn-primary">&#128424; Cetak Laporan</a>
        <a href="index.php" class="btn btn-outline-secondary">&#8592; Kembali</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>