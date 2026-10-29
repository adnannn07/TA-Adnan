<?php
require_once '../includes/fungsi.php';
requireAdmin();
require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM alternatif WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
header('Location: alternatif.php');
exit;
