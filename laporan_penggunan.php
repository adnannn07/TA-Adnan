<?php
require_once 'config/database.php';
require_once 'includes/wp_calculation.php';
require_once 'includes/fungsi.php';
require_once 'includes/upload_helper.php';

$sub_id = isset($_GET['sub']) ? intval($_GET['sub']) : 0;
if (!$sub_id) die("Sub tidak valid");

$stmt = $conn->prepare("SELECT * FROM alternatif WHERE sub_kategori_id = ?");
$stmt->bind_param('i', $sub_id);
$stmt->execute();
$altQuery = $stmt->get_result();
$alternatif = [];
while ($row = $altQuery->fetch_assoc()) $alternatif[] = $row;
$stmt->close();
if (count($alternatif)==0) die("Tidak ada data.");

$kriteria = getKriteria($conn);
$hasil = hitungWP($alternatif, $kriteria);

$stmt2 = $conn->prepare("SELECT nama FROM sub_kategori WHERE id = ?");
$stmt2->bind_param('i', $sub_id);
$stmt2->execute();
$sub = $stmt2->get_result()->fetch_assoc();
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekomendasi — <?= h($sub['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container mt-4">
    <div class="print-sheet">
        <div class="sheet-toolbar no-print">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
            <button class="btn btn-primary btn-sm" onclick="window.print()">&#128424; Cetak / Simpan sebagai PDF</button>
        </div>
        <div id="print-area">
            <div class="letterhead">
                <div class="logo-box"><?= getLogoPath() ? '<img src="'.h(getLogoPath()).'" style="width:100%;height:100%;object-fit:contain;">' : 'LOGO' ?></div>
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
            <div class="sheet-title">Laporan Rekomendasi — <?= h($sub['nama']) ?></div>
            <div class="sheet-body">
                <table class="table table-sm">
                    <thead><tr><th>Rank</th><th>Kode</th><th>Nama Paket</th><th class="text-end">Nilai V</th><th class="text-end">Harga</th></tr></thead>
                    <tbody>
                        <?php $rank=1; foreach ($hasil as $h): ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td><?= h($h['kode']) ?></td>
                            <td><?= h($h['nama']) ?></td>
                            <td class="text-end"><?= number_format($h['nilai_persen'], 4, '.', '') ?>%</td>
                            <td class="text-end">Rp <?= number_format($h['harga_total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="sign-block">
                    <div class="sign">
                        <div class="date">Jakarta, <?= formatTanggalIndonesia() ?><br>Admin Toko</div>
                        <div class="name">Adnan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
