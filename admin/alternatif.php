<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/kriteria_config.php';
require_once '../includes/upload_helper.php';
require_once '../includes/header.php';

// Proses tambah data alternatif (paket)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $sub_kategori_id = intval($_POST['sub_kategori_id'] ?? 0);
    $harga_total = floatval($_POST['harga_total'] ?? 0);
    $uploadError = null;
    $foto = null;

    // Hitung nilai_c1..c8 dari rata-rata sub-kriteria yang dipilih admin
    $nilaiKriteria = [];
    $subKriteriaValues = [];
    foreach ($KRITERIA_CONFIG as $ckey => $def) {
        $subVals = [];
        foreach ($def['sub'] as $sub) {
            $field = $ckey . '_' . $sub['key'];
            $value = floatval($_POST[$field] ?? 0.15);
            $subVals[] = $value;
            $subKriteriaValues[] = $value;
        }
        $nilaiKriteria[$ckey] = rataSubKriteria($subVals);
    }
    $nilai_c9 = hargaToTier($harga_total);

    $kode = '';
    if ($sub_kategori_id > 0) {
        $kode = generateKodeAlternatif($conn, $sub_kategori_id, $subKriteriaValues);
    }

    try {
        $foto = handleImageUpload('foto', 'uploads/produk', $kode ?: 'produk');
    } catch (Exception $e) {
        $uploadError = $e->getMessage();
    }

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
    } elseif ($nama === '' || $sub_kategori_id <= 0 || $harga_total <= 0) {
        echo "<div class='container mt-3'><div class='alert alert-danger'>Nama paket, sub kategori, dan harga total wajib diisi.</div></div>";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO alternatif
                (kode, nama, sub_kategori_id,
                 nilai_c1, nilai_c2, nilai_c3, nilai_c4, nilai_c5, nilai_c6, nilai_c7, nilai_c8, nilai_c9, harga_total,
                 detail_processor, detail_motherboard, detail_ram, detail_storage, detail_vga, detail_psu, detail_cooling, detail_casing, foto)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssiddddddddddsssssssss',
            $kode, $nama, $sub_kategori_id,
            $nilaiKriteria['c1'], $nilaiKriteria['c2'], $nilaiKriteria['c3'], $nilaiKriteria['c4'],
            $nilaiKriteria['c5'], $nilaiKriteria['c6'], $nilaiKriteria['c7'], $nilaiKriteria['c8'],
            $nilai_c9, $harga_total,
            $detail_processor, $detail_motherboard, $detail_ram, $detail_storage, $detail_vga, $detail_psu, $detail_cooling, $detail_casing, $foto
        );
        $stmt->execute();
        $stmt->close();
        echo "<div class='container mt-3'><div class='alert alert-success'>Data tersimpan. Nilai kriteria dihitung otomatis dari sub-kriteria yang dipilih.</div></div>";
    }
}

// Tampil data
$data = $conn->query("SELECT a.*, s.nama as sub_nama, k.nama as kat_nama
                      FROM alternatif a
                      JOIN sub_kategori s ON a.sub_kategori_id = s.id
                      JOIN kategori k ON s.kategori_id = k.id
                      ORDER BY a.id");
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3 class="mb-0">Kelola Alternatif (Paket)</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Paket</button>
    </div>
    <div class="mb-3">
        <input type="text" id="searchAlt" class="form-control" placeholder="Cari kode, nama paket, atau kategori..." onkeyup="filterAltTable()">
    </div>
    <div class="table-responsive">
    <table class="table table-hover align-middle" id="altTable">
        <thead><tr><th></th><th>Kode</th><th>Nama Paket</th><th>Kategori</th><th>Sub Kategori</th><th class="text-end">Harga</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php while($row = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $row['foto'] ? '<img src="../'.h($row['foto']).'" class="thumb-list">' : '<div class="thumb-list-placeholder">—</div>' ?></td>
                <td><span class="badge-kode"><?= h($row['kode']) ?></span></td>
                <td class="fw-semibold"><?= h($row['nama']) ?></td>
                <td><?= h($row['kat_nama']) ?></td>
                <td><?= h($row['sub_nama']) ?></td>
                <td class="text-end">Rp <?= number_format($row['harga_total'],0,',','.') ?></td>
                <td>
                    <a href="alternatif_edit.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="alternatif_hapus.php?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus paket ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
<script>
function filterAltTable(){
    const q = document.getElementById('searchAlt').value.toLowerCase();
    document.querySelectorAll('#altTable tbody tr').forEach(tr=>{
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header"><h5>Tambah Paket</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-md-4"><label>Nama Paket</label><input type="text" name="nama" class="form-control" required></div>
                <div class="col-md-5"></div>
                <div class="col-md-3">
                    <label>Sub Kategori</label>
                    <select name="sub_kategori_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php $subs = $conn->query("SELECT s.*, k.nama as kat_nama FROM sub_kategori s JOIN kategori k ON s.kategori_id=k.id ORDER BY k.nama, s.nama"); while($s=$subs->fetch_assoc()): ?>
                        <option value="<?= (int) $s['id'] ?>"><?= h($s['kat_nama']) ?> - <?= h($s['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <hr>
            <p class="text-muted mb-2">Pilih tingkatan tiap sub-kriteria (Rendah 0.15 / Sedang 0.35 / Tinggi 0.50) sesuai spesifikasi komponen. Nilai C1-C8 dihitung otomatis sebagai rata-rata. Jika kode dikosongkan, sistem akan menghasilkan kode alternatif secara otomatis.</p>

            <div class="row g-3">
                <?php foreach ($KRITERIA_CONFIG as $ckey => $def): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header py-2"><?= strtoupper($ckey) ?> — <?= h($def['nama']) ?> <span class="text-muted">(bobot <?= number_format($def['bobot'],2) ?>)</span></div>
                        <div class="card-body py-2">
                            <?php foreach ($def['sub'] as $sub): ?>
                            <div class="mb-2">
                                <label class="mb-1"><?= h($sub['label']) ?></label>
                                <select name="<?= $ckey ?>_<?= $sub['key'] ?>" class="form-select form-select-sm" required>
                                    <?php foreach ($sub['options'] as $opt): ?>
                                    <option value="<?= $opt['val'] ?>"><?= h($opt['label']) ?> (<?= number_format($opt['val'],2) ?>)</option>
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
                        <div class="card-header py-2">C9 — Harga Total <span class="text-muted">(bobot 0.05, cost)</span></div>
                        <div class="card-body py-2">
                            <label class="mb-1">Total Harga Paket (Rp)</label>
                            <input type="number" step="1" min="1" name="harga_total" class="form-control form-control-sm" required>
                            <div class="form-text">Tier otomatis: ≤ Rp 9.999.999 = Murah (0.50) &middot; Rp 10-19,99jt = Sedang (0.35) &middot; ≥ Rp 20jt = Mahal (0.15).</div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <p class="text-muted mb-1">Detail komponen (ditampilkan di halaman detail paket)</p>
            <div class="row g-2">
                <div class="col-md-6"><label>Detail Processor</label><input type="text" name="detail_processor" class="form-control"></div>
                <div class="col-md-6"><label>Detail Motherboard</label><input type="text" name="detail_motherboard" class="form-control"></div>
                <div class="col-md-6"><label>Detail RAM</label><input type="text" name="detail_ram" class="form-control"></div>
                <div class="col-md-6"><label>Detail Storage</label><input type="text" name="detail_storage" class="form-control"></div>
                <div class="col-md-6"><label>Detail VGA/GPU</label><input type="text" name="detail_vga" class="form-control"></div>
                <div class="col-md-6"><label>Detail PSU</label><input type="text" name="detail_psu" class="form-control"></div>
                <div class="col-md-6"><label>Detail Cooling</label><input type="text" name="detail_cooling" class="form-control"></div>
                <div class="col-md-6"><label>Detail Casing</label><input type="text" name="detail_casing" class="form-control"></div>
            </div>

            <hr>
            <p class="text-muted mb-1">Foto Produk</p>
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control">
                    <div class="form-text">Opsional. Format JPG/PNG/WEBP, maksimal 2MB.</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
