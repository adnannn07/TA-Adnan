<?php
require_once '../includes/fungsi.php';
require_once '../config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['masuk'])) {
    $nama_pengguna = trim($_POST['nama_pengguna'] ?? '');
    $kata_sandi = md5($_POST['kata_sandi'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $nama_pengguna, $kata_sandi);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $_SESSION['admin'] = $nama_pengguna;
        $stmt->close();
        header('Location: index.php');
        exit;
    } else {
        $error = "Nama pengguna atau kata sandi salah!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Masuk Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:430px;">
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <img src="../assets/img/logovj.png" alt="Logo Vira Jaya Computer" style="width:90px;height:90px;object-fit:cover;border-radius:16px;">
            </div>
            <div class="text-center mb-4">
                <h4 class="mb-1" style="color:#14213D; font-weight:700;">Masuk Admin</h4>
                <small class="text-muted">Vira Jaya Computer</small>
            </div>
            <?php if($error): ?><div class='alert alert-danger'><?= h($error) ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Kata Sandi</label>
                    <input type="password" name="kata_sandi" class="form-control" required>
                </div>
                <button type="submit" name="masuk" class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
