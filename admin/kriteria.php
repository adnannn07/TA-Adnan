<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/header.php';

$kriteria = $conn->query("SELECT * FROM kriteria ORDER BY id");
?>
<div class="container mt-4">
    <div class="hero-panel">
        <h2>Data Kriteria &amp; Bobot</h2>
        <p>9 kriteria metode Weighted Product.</p>
    </div>
    <div class="table-responsive">
    <table class="table table-hover">
        <thead><tr><th>Kode</th><th>Kriteria</th><th class="text-end">Bobot</th><th>Atribut</th></tr></thead>
        <tbody>
            <?php $total=0; while($k = $kriteria->fetch_assoc()): $total += $k['bobot']; ?>
            <tr>
                <td><span class="badge-kode"><?= h($k['kode']) ?></span></td>
                <td class="fw-semibold"><?= h($k['nama']) ?></td>
                <td class="text-end"><?= number_format($k['bobot'],2) ?></td>
                <td><span class="badge <?= $k['atribut']=='benefit' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($k['atribut']) ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot><tr class="fw-bold"><td colspan="2">Total Bobot</td><td class="text-end"><?= number_format($total,2) ?></td><td></td></tr></tfoot>
    </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
