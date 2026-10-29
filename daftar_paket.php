<?php
require_once 'config/database.php';
require_once 'includes/fungsi.php';
require_once 'includes/header.php';

$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama");
$kategoriRows = [];
while ($k = $kategoriList->fetch_assoc()) $kategoriRows[] = $k;

$selectedKategori = isset($_GET['kat']) ? array_map('intval', (array) $_GET['kat']) : [];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

if (count($selectedKategori) > 0) {
    $placeholders = implode(',', array_fill(0, count($selectedKategori), '?'));
    $types = str_repeat('i', count($selectedKategori));

    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM alternatif a JOIN sub_kategori s ON a.sub_kategori_id=s.id WHERE s.kategori_id IN ($placeholders)");
    $countStmt->bind_param($types, ...$selectedKategori);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    $sql = "SELECT a.*, k.nama as kat_nama FROM alternatif a
            JOIN sub_kategori s ON a.sub_kategori_id=s.id
            JOIN kategori k ON s.kategori_id=k.id
            WHERE s.kategori_id IN ($placeholders)
            ORDER BY a.nama LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $allTypes = $types . 'ii';
    $params = array_merge($selectedKategori, [$perPage, $offset]);
    $stmt->bind_param($allTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $total = $conn->query("SELECT COUNT(*) as total FROM alternatif")->fetch_assoc()['total'];
    $stmt = $conn->prepare("SELECT a.*, k.nama as kat_nama FROM alternatif a
            JOIN sub_kategori s ON a.sub_kategori_id=s.id
            JOIN kategori k ON s.kategori_id=k.id
            ORDER BY a.nama LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}
$totalPages = max(1, ceil($total / $perPage));
?>
<div class="container mt-4">
    <h3 class="mb-3">Daftar Paket Rakitan Komputer</h3>

    <form method="GET" class="filter-panel">
        <strong class="me-3">Filter Kategori:</strong>
        <?php foreach ($kategoriRows as $k): ?>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="kat[]" value="<?= (int) $k['id'] ?>"
                id="kat<?= (int) $k['id'] ?>" <?= in_array($k['id'], $selectedKategori) ? 'checked' : '' ?>
                onchange="this.form.submit()">
            <label class="form-check-label" for="kat<?= (int) $k['id'] ?>"><?= h($k['nama']) ?></label>
        </div>
        <?php endforeach; ?>
        <?php if (count($selectedKategori) > 0): ?>
        <a href="daftar_paket.php" class="btn btn-sm btn-outline-secondary ms-2">Reset Filter</a>
        <?php endif; ?>
    </form>

    <p class="text-muted"><?= $total ?> paket ditemukan</p>

    <div class="row g-3">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card product-card h-100">
                <div class="card-body d-flex justify-content-between align-items-start gap-3">
                    <?php if ($row['foto']): ?>
                    <img src="<?= h($row['foto']) ?>" class="thumb-list" style="width:64px;height:64px;flex-shrink:0;">
                    <?php else: ?>
                    <div class="thumb-list-placeholder" style="width:64px;height:64px;flex-shrink:0;">Produk</div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <span class="badge bg-secondary mb-2"><?= h($row['kat_nama']) ?></span>
                        <h6 class="card-title mb-1"><?= h($row['nama']) ?></h6>
                        <p class="text-muted small mb-1">Processor: <?= h($row['detail_processor']) ?></p>
                        <p class="text-muted small mb-2">VGA: <?= h($row['detail_vga']) ?></p>
                        <p class="fw-bold mb-0">Rp <?= number_format($row['harga_total'],0,',','.') ?></p>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <a href="detail.php?id=<?= (int) $row['id'] ?>" class="btn btn-primary btn-sm w-100">Lihat Detail</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $totalPages; $p++):
                $qs = $_GET; $qs['page'] = $p; $url = 'daftar_paket.php?' . http_build_query($qs); ?>
            <li class="page-item <?= $p == $page ? 'active' : '' ?>"><a class="page-link" href="<?= h($url) ?>"><?= $p ?></a></li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
