<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/wp_calculation.php';
require_once '../includes/upload_helper.php';
require_once '../includes/header.php';

$jenis = $_GET['jenis'] ?? '';

$reportDefs = [
    'semua'      => ['title' => 'Laporan Seluruh Data Paket', 'icon' => '&#128190;', 'desc' => 'Daftar lengkap seluruh paket rakitan komputer beserta kategori dan harga.'],
    'kategori'   => ['title' => 'Laporan Data Paket per Kategori', 'icon' => '&#128193;', 'desc' => 'Rekap jumlah dan daftar paket, dikelompokkan per kategori utama.'],
    'ranking'    => ['title' => 'Laporan Ranking Rekomendasi (WP)', 'icon' => '&#127942;', 'desc' => 'Hasil perhitungan Weighted Product lengkap untuk satu sub-kategori kebutuhan.'],
    'kriteria'   => ['title' => 'Laporan Kriteria & Bobot', 'icon' => '&#9878;', 'desc' => 'Daftar 9 kriteria WP beserta bobot dan atributnya.'],
    'stok_harga' => ['title' => 'Laporan Rekap Harga per Kategori', 'icon' => '&#128176;', 'desc' => 'Ringkasan jumlah paket serta harga termurah, termahal, dan rata-rata per kategori.'],
];

// ================= Menu (tanpa parameter jenis) =================
if ($jenis === '') {
?>
<div class="container mt-4">
    <div class="hero-panel">
        <h2>Cetak Laporan</h2>
        <p>Pilih jenis laporan untuk melihat pratinjau dan mencetaknya sebagai PDF.</p>
    </div>
    <div class="row g-3">
        <?php foreach ($reportDefs as $key => $r): ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="fs-3 mb-2"><?= $r['icon'] ?></div>
                    <h6 class="card-title"><?= h($r['title']) ?></h6>
                    <p class="text-muted small flex-grow-1"><?= h($r['desc']) ?></p>
                    <?php if ($key === 'ranking'): ?>
                    <form method="GET" class="mt-2">
                        <input type="hidden" name="jenis" value="ranking">
                        <select name="sub_id" class="form-select form-select-sm mb-2" required>
                            <option value="">-- Pilih Sub Kategori --</option>
                            <?php $subs = $conn->query("SELECT s.*, k.nama as kat_nama FROM sub_kategori s JOIN kategori k ON s.kategori_id=k.id ORDER BY k.nama, s.nama");
                            while ($s = $subs->fetch_assoc()): ?>
                            <option value="<?= (int) $s['id'] ?>"><?= h($s['kat_nama']) ?> - <?= h($s['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">Buka Laporan</button>
                    </form>
                    <?php else: ?>
                    <a href="laporan.php?jenis=<?= $key ?>" class="btn btn-outline-primary btn-sm">Buka Laporan &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
require_once '../includes/footer.php';
exit;
}

// ================= Render laporan terpilih =================
$def = $reportDefs[$jenis] ?? null;
if (!$def) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Jenis laporan tidak dikenal.</div></div>";
    require_once '../includes/footer.php';
    exit;
}

$logoPath = getLogoPath();
if (!$logoPath && file_exists(__DIR__ . '/../assets/img/logovj.png')) {
    $logoPath = 'assets/img/logovj.png';
}
$logoSrc = $logoPath ? '../' . $logoPath : '';

$tableHtml = '';

if ($jenis === 'semua') {
    $data = $conn->query("SELECT a.*, k.nama as kat_nama, s.nama as sub_nama FROM alternatif a
                          JOIN sub_kategori s ON a.sub_kategori_id=s.id
                          JOIN kategori k ON s.kategori_id=k.id ORDER BY k.nama, a.nama");
    $tableHtml = '<table class="table table-sm"><thead><tr><th>Kode</th><th>Nama Paket</th><th>Kategori</th><th>Sub Kategori</th><th class="text-end">Harga</th></tr></thead><tbody>';
    while ($row = $data->fetch_assoc()) {
        $tableHtml .= '<tr><td>'.h($row['kode']).'</td><td>'.h($row['nama']).'</td><td>'.h($row['kat_nama']).'</td><td>'.h($row['sub_nama']).'</td><td class="text-end">Rp '.number_format($row['harga_total'],0,',','.').'</td></tr>';
    }
    $tableHtml .= '</tbody></table>';
}

if ($jenis === 'kategori') {
    $kats = $conn->query("SELECT * FROM kategori ORDER BY nama");
    while ($kat = $kats->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT a.* FROM alternatif a JOIN sub_kategori s ON a.sub_kategori_id=s.id WHERE s.kategori_id=? ORDER BY a.nama");
        $stmt->bind_param('i', $kat['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $tableHtml .= '<h6 class="mt-3 mb-2">'.h($kat['nama']).'</h6><table class="table table-sm"><thead><tr><th>Kode</th><th>Nama Paket</th><th class="text-end">Harga</th></tr></thead><tbody>';
        while ($row = $res->fetch_assoc()) {
            $tableHtml .= '<tr><td>'.h($row['kode']).'</td><td>'.h($row['nama']).'</td><td class="text-end">Rp '.number_format($row['harga_total'],0,',','.').'</td></tr>';
        }
        $tableHtml .= '</tbody></table>';
        $stmt->close();
    }
}

if ($jenis === 'ranking') {
    $sub_id = intval($_GET['sub_id'] ?? 0);
    if ($sub_id <= 0) {
        $tableHtml = '<p class="text-danger">Sub kategori tidak valid. Kembali ke menu dan pilih sub kategori.</p>';
    } else {
        $stmt = $conn->prepare("SELECT nama FROM sub_kategori WHERE id=?");
        $stmt->bind_param('i', $sub_id); $stmt->execute();
        $subNama = $stmt->get_result()->fetch_assoc()['nama'] ?? '-';
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM alternatif WHERE sub_kategori_id=?");
        $stmt->bind_param('i', $sub_id); $stmt->execute();
        $res = $stmt->get_result();
        $alts = [];
        while ($row = $res->fetch_assoc()) $alts[] = $row;
        $stmt->close();

        $kriteriaAll = getKriteria($conn);
        $hasil = hitungWP($alts, $kriteriaAll);
        $def['title'] .= ' — ' . $subNama;

        $tableHtml = '<table class="table table-sm"><thead><tr><th>Rank</th><th>Kode</th><th>Nama Paket</th><th class="text-end">Nilai S</th><th class="text-end">Nilai V</th><th class="text-end">Harga</th></tr></thead><tbody>';
        $rank = 1;
        foreach ($hasil as $h) {
            $tableHtml .= '<tr><td>'.$rank++.'</td><td>'.h($h['kode']).'</td><td>'.h($h['nama']).'</td><td class="text-end">'.number_format($h['nilai_S'],6).'</td><td class="text-end">'.number_format($h['nilai_persen'],4,'.','').'%</td><td class="text-end">Rp '.number_format($h['harga_total'],0,',','.').'</td></tr>';
        }
        $tableHtml .= '</tbody></table>';
    }
}

if ($jenis === 'kriteria') {
    $krit = $conn->query("SELECT * FROM kriteria ORDER BY id");
    $tableHtml = '<table class="table table-sm"><thead><tr><th>Kode</th><th>Kriteria</th><th class="text-end">Bobot</th><th>Atribut</th></tr></thead><tbody>';
    $total = 0;
    while ($k = $krit->fetch_assoc()) {
        $total += $k['bobot'];
        $tableHtml .= '<tr><td>'.h($k['kode']).'</td><td>'.h($k['nama']).'</td><td class="text-end">'.number_format($k['bobot'],2).'</td><td>'.ucfirst($k['atribut']).'</td></tr>';
    }
    $tableHtml .= '</tbody><tfoot><tr class="fw-bold"><td colspan="2">Total Bobot</td><td class="text-end">'.number_format($total,2).'</td><td></td></tr></tfoot></table>';
}

if ($jenis === 'stok_harga') {
    $kats = $conn->query("SELECT * FROM kategori ORDER BY nama");
    $tableHtml = '<table class="table table-sm"><thead><tr><th>Kategori</th><th class="text-end">Jumlah Paket</th><th class="text-end">Harga Termurah</th><th class="text-end">Harga Termahal</th><th class="text-end">Rata-rata</th></tr></thead><tbody>';
    while ($kat = $kats->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT COUNT(*) jml, MIN(a.harga_total) mn, MAX(a.harga_total) mx, AVG(a.harga_total) av
                                FROM alternatif a JOIN sub_kategori s ON a.sub_kategori_id=s.id WHERE s.kategori_id=?");
        $stmt->bind_param('i', $kat['id']); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($r['jml'] > 0) {
            $tableHtml .= '<tr><td>'.h($kat['nama']).'</td><td class="text-end">'.$r['jml'].'</td><td class="text-end">Rp '.number_format($r['mn'],0,',','.').'</td><td class="text-end">Rp '.number_format($r['mx'],0,',','.').'</td><td class="text-end">Rp '.number_format($r['av'],0,',','.').'</td></tr>';
        }
    }
    $tableHtml .= '</tbody></table>';
}
?>
<div class="print-sheet">
    <div class="sheet-toolbar no-print">
        <a href="laporan.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Daftar Laporan</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()">&#128424; Cetak / Simpan sebagai PDF</button>
    </div>
    <div id="print-area">
        <div class="letterhead">
            <div class="logo-box"><?= $logoSrc ? '<img src="'.h($logoSrc).'" style="width:100%;height:100%;object-fit:contain;">' : 'LOGO' ?></div>
            <div style="flex:1;">
                <div class="co-name">VIRA JAYA COMPUTER</div>
                <div class="co-addr">
                    No Telp 08123456789<br>
                    Mangga Dua Mall Lt.4 No.101 A Jalan Arteri, Jl. Mangga Dua Raya, RT.1/RW.12, Mangga Dua Sel.,
                    Kecamatan Sawah Besar, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10730
                </div>
            </div>
            <div style="width:64px;"></div>
        </div>
        <div class="sheet-title"><?= h($def['title']) ?></div>
        <div class="sheet-body">
            <?= $tableHtml ?>
            <div class="sign-block">
                <div class="sign">
                    <div class="date">Jakarta, <?= formatTanggalIndonesia() ?><br>Admin Toko</div>
                    <div class="name">Adnan</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
