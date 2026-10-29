<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/header.php';

// Tambah kategori utama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $nama = trim($_POST['nama'] ?? '');
    if ($nama !== '') {
        $stmt = $conn->prepare("INSERT INTO kategori (nama) VALUES (?)");
        $stmt->bind_param('s', $nama);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: kategori.php');
    exit;
}

// Tambah sub kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_sub'])) {
    $kategori_id = intval($_POST['kategori_id'] ?? 0);
    $nama_sub = trim($_POST['nama_sub'] ?? '');
    if ($kategori_id > 0 && $nama_sub !== '') {
        $stmt = $conn->prepare("INSERT INTO sub_kategori (kategori_id, nama) VALUES (?, ?)");
        $stmt->bind_param('is', $kategori_id, $nama_sub);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: kategori.php');
    exit;
}

// Hapus kategori
if (isset($_GET['hapus_kategori'])) {
    $id = intval($_GET['hapus_kategori']);
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: kategori.php');
    exit;
}

// Hapus sub kategori
if (isset($_GET['hapus_sub'])) {
    $id = intval($_GET['hapus_sub']);
    $stmt = $conn->prepare("DELETE FROM sub_kategori WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: kategori.php');
    exit;
}

$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama");
?>
<div class="container mt-4">
    <h3>Kelola Kategori & Sub Kategori</h3>

    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">Tambah Kategori Utama</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <input type="text" name="nama" class="form-control" placeholder="Nama kategori" required>
                        </div>
                        <button type="submit" name="tambah_kategori" class="btn btn-primary btn-sm">Tambah</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Tambah Sub Kategori</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $katOpt = $conn->query("SELECT * FROM kategori ORDER BY nama");
                                while ($k = $katOpt->fetch_assoc()):
                                ?>
                                <option value="<?= (int) $k['id'] ?>"><?= h($k['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="nama_sub" class="form-control" placeholder="Nama sub kategori" required>
                        </div>
                        <button type="submit" name="tambah_sub" class="btn btn-primary btn-sm">Tambah</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <?php while ($kat = $kategoriList->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?= h($kat['nama']) ?></strong>
                    <a href="kategori.php?hapus_kategori=<?= (int) $kat['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Hapus kategori ini? Sub kategori di dalamnya juga akan terhapus.')">Hapus</a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php
                    $subStmt = $conn->prepare("SELECT * FROM sub_kategori WHERE kategori_id = ? ORDER BY nama");
                    $katId = (int) $kat['id'];
                    $subStmt->bind_param('i', $katId);
                    $subStmt->execute();
                    $subs = $subStmt->get_result();
                    if ($subs->num_rows === 0):
                    ?>
                    <li class="list-group-item text-muted">Belum ada sub kategori.</li>
                    <?php else: while ($s = $subs->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($s['nama']) ?>
                        <a href="kategori.php?hapus_sub=<?= (int) $s['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Hapus sub kategori ini?')">Hapus</a>
                    </li>
                    <?php endwhile; endif; $subStmt->close(); ?>
                </ul>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
