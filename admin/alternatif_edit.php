<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/kriteria_config.php';
require_once '../includes/upload_helper.php';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
echo "<div class='container mt-4'><div class='alert alert-danger'>ID tidak valid.</div></div>";
require_once '../includes/footer.php';
exit;
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $sub_kategori_id = intval($_POST['sub_kategori_id'] ?? 0);
    $harga_total = floatval($_POST['harga_total'] ?? 0);

    // Ambil foto lama untuk dipertahankan jika tidak ada upload baru
    $stmtOld = $conn->prepare("SELECT foto FROM alternatif WHERE id = ?");
    $stmtOld->bind_param('i', $id);
    $stmtOld->execute();
    $fotoLama = $stmtOld->get_result()->fetch_assoc()['foto'] ?? null;
    $stmtOld->close();

    $uploadError = null;
    $foto = $fotoLama; // default: pertahankan foto lama
    try {
        $fotoBaru = handleImageUpload('foto', 'uploads/produk', $kode ?: 'produk');
        if ($fotoBaru !== null) {
            deleteOldImage($fotoLama);
            $foto = $fotoBaru;
        }
    } catch (Exception $e) {
        $uploadError = $e->getMessage();
    }

    $nilaiKriteria = [];
    foreach ($KRITERIA_CONFIG as $ckey => $def) {
        $subVals = [];
        foreach ($def['sub'] as $sub) {
            $field = $ckey . '_' . $sub['key'];
            $subVals[] = floatval($_POST[$field] ?? 0.15);
        }
        $nilaiKriteria[$ckey] = rataSubKriteria($subVals);
    }
    $nilai_c9 = hargaToTier($harga_total);

    $detail_processor   = trim($_POST['detail_processor'] ?? '');
    $detail_motherboard = trim($_POST['detail_motherboard'] ?? '');
    $detail_ram         = trim($_POST['detail_ram'] ?? '');
    $detail_storage     = trim($_POST['detail_storage'] ?? '');
    $detail_vga         = trim($_POST['detail_vga'] ?? '');
    $detail_psu         = trim($_POST['detail_psu'] ?? '');
    $detail_cooling     = trim($_POST['detail_cooling'] ?? '');
    $detail_casing      = trim($_POST['detail_casing'] ?? '');

    if ($uploadError) {
        echo "<div class='container mt-3'><div class='alert alert-danger'>Foto tidak tersimpan: " . h($uploadError) . "</div></div>";
    } elseif ($kode === '' || $nama === '' || $sub_kategori_id <= 0 || $harga_total <= 0) {
        echo "<div class='container mt-3'><div class='alert alert-danger'>Kode, nama, sub kategori, dan harga total wajib diisi.</div></div>";
    } else {
        $stmt = $conn->prepare(
            "UPDATE alternatif SET
                kode = ?, nama = ?, sub_kategori_id = ?,
                nilai_c1 = ?, nilai_c2 = ?, nilai_c3 = ?, nilai_c4 = ?, nilai_c5 = ?, nilai_c6 = ?, nilai_c7 = ?, nilai_c8 = ?, nilai_c9 = ?, harga_total = ?,
                detail_processor = ?, detail_motherboard = ?, detail_ram = ?, detail_storage = ?, detail_vga = ?, detail_psu = ?, detail_cooling = ?, detail_casing = ?, foto = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            'ssiddddddddddsssssssssi',
            $kode, $nama, $sub_kategori_id,
            $nilaiKriteria['c1'], $nilaiKriteria['c2'], $nilaiKriteria['c3'], $nilaiKriteria['c4'],
            $nilaiKriteria['c5'], $nilaiKriteria['c6'], $nilaiKriteria['c7'], $nilaiKriteria['c8'],
            $nilai_c9, $harga_total,
            $detail_processor, $detail_motherboard, $detail_ram, $detail_storage, $detail_vga, $detail_psu, $detail_cooling, $detail_casing, $foto,
            $id
        );
        $stmt->execute();
        $stmt->close();
        header('Location: alternatif.php');
        exit;
    }
}

// Ambil data existing untuk mengisi form
$stmt = $conn->prepare("SELECT * FROM alternatif WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$alt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alt) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Paket tidak ditemukan.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

// Rekonstruksi kombinasi sub-kriteria dari nilai_cX yang tersimpan, untuk pre-select dropdown
$rekonstruksi = [];
foreach ($KRITERIA_CONFIG as $ckey => $def) {
    $jumlahSub = count($def['sub']);
    $rekonstruksi[$ckey] = reconstructSubValues($jumlahSub, (float) $alt['nilai_c' . substr($ckey,1)]);
}

$subs = $conn->query("SELECT s.*, k.nama as kat_nama FROM sub_kategori s JOIN kategori k ON s.kategori_id=k.id ORDER BY k.nama, s.nama");
?>
<div class="container mt-4">
    <div class="hero-panel">
        <h2>Edit Paket: <?= h($alt['nama']) ?></h2>
        <p>Ubah sub-kriteria di bawah untuk memperbarui perhitungan skor WP secara otomatis.</p>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="row g-2">
            <div class="col-md-4"><label>Kode</label><input type="text" name="kode" class="form-control" value="<?= h($alt['kode']) ?>" required></div>
            <div class="col-md-5"><label>Nama Paket</label><input type="text" name="nama" class="form-control" value="<?= h($alt['nama']) ?>" required></div>
            <div class="col-md-3">
                <label>Sub Kategori</label>
                <select name="sub_kategori_id" class="form-select" required>
                    <?php while($s = $subs->fetch_assoc()): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= ($s['id'] == $alt['sub_kategori_id']) ? 'selected' : '' ?>><?= h($s['kat_nama']) ?> - <?= h($s['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <hr>
        <p class="text-muted mb-2">Sub-kriteria (Rendah 0.15 / Sedang 0.35 / Tinggi 0.50) — sudah dipilihkan otomatis sesuai data tersimpan.</p>

        <div class="row g-3">
            <?php foreach ($KRITERIA_CONFIG as $ckey => $def): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <?= strtoupper($ckey) ?> — <?= h($def['nama']) ?>
                        <span class="text-muted">(bobot <?= number_format($def['bobot'],2) ?>, saat ini: <?= number_format($alt['nilai_c'.substr($ckey,1)],2) ?>)</span>
                    </div>
                    <div class="card-body py-2">
                        <?php foreach ($def['sub'] as $i => $sub): $current = $rekonstruksi[$ckey][$i]; ?>
                        <div class="mb-2">
                            <label class="mb-1"><?= h($sub['label']) ?></label>
                            <select name="<?= $ckey ?>_<?= $sub['key'] ?>" class="form-select form-select-sm" required>
                                <?php foreach ($sub['options'] as $opt): ?>
                                <option value="<?= $opt['val'] ?>" <?= (abs($opt['val']-$current) < 0.001) ? 'selected' : '' ?>><?= h($opt['label']) ?> (<?= number_format($opt['val'],2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header py-2">C9 — Harga Total <span class="text-muted">(bobot 0.05, cost, saat ini: <?= number_format($alt['nilai_c9'],2) ?>)</span></div>
                    <div class="card-body py-2">
                        <label class="mb-1">Total Harga Paket (Rp)</label>
                        <input type="number" step="1" min="1" name="harga_total" class="form-control form-control-sm" value="<?= h($alt['harga_total']) ?>" required>
                        <div class="form-text">Tier otomatis: ≤ Rp 9.999.999 = Murah (0.50) &middot; Rp 10-19,99jt = Sedang (0.35) &middot; ≥ Rp 20jt = Mahal (0.15).</div>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <p class="text-muted mb-1">Detail komponen</p>
        <div class="row g-2">
            <?php
            $detailFields = [
                'detail_processor' => 'Processor', 'detail_motherboard' => 'Motherboard',
                'detail_ram' => 'RAM', 'detail_storage' => 'Storage',
                'detail_vga' => 'VGA/GPU', 'detail_psu' => 'PSU',
                'detail_cooling' => 'Cooling', 'detail_casing' => 'Casing',
            ];
            foreach ($detailFields as $field => $label): ?>
            <div class="col-md-6">
                <label>Detail <?= h($label) ?></label>
                <input type="text" name="<?= h($field) ?>" class="form-control" value="<?= h($alt[$field]) ?>">
            </div>
            <?php endforeach; ?>
        </div>

        <hr>
        <p class="text-muted mb-1">Foto Produk</p>
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <?php if ($alt['foto']): ?>
                <img src="../<?= h($alt['foto']) ?>" class="thumb-list" style="width:64px;height:64px;">
                <?php else: ?>
                <div class="thumb-list-placeholder" style="width:64px;height:64px;">Belum ada</div>
                <?php endif; ?>
            </div>
            <div class="col">
                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control">
                <div class="form-text">Kosongkan jika tidak ingin mengganti foto. Format JPG/PNG/WEBP, maksimal 2MB.</div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="simpan" class="btn btn-primary">Simpan Perubahan</button>
            <a href="alternatif.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
