<?php
require_once 'config/database.php';
require_once 'includes/fungsi.php';
require_once 'includes/wp_calculation.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT a.*, s.nama as sub_nama, k.nama as kat_nama 
                      FROM alternatif a 
                      JOIN sub_kategori s ON a.sub_kategori_id = s.id
                      JOIN kategori k ON s.kategori_id = k.id
                      WHERE a.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$alt = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$alt) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Paket tidak ditemukan.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

$kriteriaAll = getKriteria($conn);
$rankingAlternatif = [];
$stmt = $conn->prepare("SELECT * FROM alternatif WHERE sub_kategori_id = ?");
$stmt->bind_param('i', $alt['sub_kategori_id']);
$stmt->execute();
$rankingRes = $stmt->get_result();
while ($row = $rankingRes->fetch_assoc()) {
    $rankingAlternatif[] = $row;
}
$stmt->close();
$rankingHasil = count($rankingAlternatif) ? hitungWP($rankingAlternatif, $kriteriaAll) : [];
$rankInfo = null;
foreach ($rankingHasil as $idx => $item) {
    if ((int) $item['id'] === (int) $alt['id']) {
        $rankInfo = ['rank' => $idx + 1, 'nilai_V' => $item['nilai_V'], 'nilai_persen' => $item['nilai_persen'] ?? ($item['nilai_V'] * 100)];
        break;
    }
}
?>

<div class="container mt-4">
    <div class="hero-panel">
        <h2><?= h($alt['nama']) ?></h2>
        <p><span class="badge-kode"><?= h($alt['kode']) ?></span> &nbsp; <?= h($alt['kat_nama']) ?> &raquo; <?= h($alt['sub_nama']) ?></p>
        <?php if ($rankInfo): ?>
        <div class="mt-2">
            <span class="badge bg-primary">Peringkat #<?= (int) $rankInfo['rank'] ?></span>
            <span class="badge bg-success">Vektor V: <?= number_format($rankInfo['nilai_persen'], 4, '.', '') ?>%</span>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($alt['foto']): ?>
    <div class="text-center mb-3">
        <img src="<?= h($alt['foto']) ?>" style="max-height:220px;border-radius:10px;border:1px solid var(--border);">
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Spesifikasi Komponen</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Processor:</strong> <?= h($alt['detail_processor']) ?></li>
                    <li class="list-group-item"><strong>Motherboard:</strong> <?= h($alt['detail_motherboard']) ?></li>
                    <li class="list-group-item"><strong>RAM:</strong> <?= h($alt['detail_ram']) ?></li>
                    <li class="list-group-item"><strong>Storage:</strong> <?= h($alt['detail_storage']) ?></li>
                    <li class="list-group-item"><strong>VGA/GPU:</strong> <?= h($alt['detail_vga']) ?></li>
                    <li class="list-group-item"><strong>PSU:</strong> <?= h($alt['detail_psu']) ?></li>
                    <li class="list-group-item"><strong>Cooling:</strong> <?= h($alt['detail_cooling']) ?></li>
                    <li class="list-group-item"><strong>Casing:</strong> <?= h($alt['detail_casing']) ?></li>
                    <li class="list-group-item bg-light"><strong>Total Harga:</strong> Rp <?= number_format($alt['harga_total'], 0, ',', '.') ?></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Skor Kriteria (WP)</div>
                <table class="table table-sm mb-0">
                    <tr><td>C1 Processor <span class="text-muted">(0.20)</span></td><td class="text-end"><?= number_format($alt['nilai_c1'],2) ?></td></tr>
                    <tr><td>C2 Motherboard <span class="text-muted">(0.10)</span></td><td class="text-end"><?= number_format($alt['nilai_c2'],2) ?></td></tr>
                    <tr><td>C3 RAM <span class="text-muted">(0.15)</span></td><td class="text-end"><?= number_format($alt['nilai_c3'],2) ?></td></tr>
                    <tr><td>C4 Storage <span class="text-muted">(0.12)</span></td><td class="text-end"><?= number_format($alt['nilai_c4'],2) ?></td></tr>
                    <tr><td>C5 VGA/GPU <span class="text-muted">(0.18)</span></td><td class="text-end"><?= number_format($alt['nilai_c5'],2) ?></td></tr>
                    <tr><td>C6 PSU <span class="text-muted">(0.08)</span></td><td class="text-end"><?= number_format($alt['nilai_c6'],2) ?></td></tr>
                    <tr><td>C7 Cooling <span class="text-muted">(0.07)</span></td><td class="text-end"><?= number_format($alt['nilai_c7'],2) ?></td></tr>
                    <tr><td>C8 Casing <span class="text-muted">(0.05)</span></td><td class="text-end"><?= number_format($alt['nilai_c8'],2) ?></td></tr>
                    <tr class="fw-bold"><td>C9 Harga Total <span class="text-muted fw-normal">(0.05, cost — tier: <?= number_format($alt['nilai_c9'],2) ?>)</span></td><td class="text-end">Rp <?= number_format($alt['harga_total'],0,',','.') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <a href="javascript:history.back()" class="btn btn-outline-secondary mt-3">&#8592; Kembali</a>
</div>

<?php require_once 'includes/footer.php'; ?>