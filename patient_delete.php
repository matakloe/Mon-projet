<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT photo FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if ($patient) {
    if ($patient['photo']) {
        $path = __DIR__ . '/uploads/patients/' . $patient['photo'];
        if (is_file($path)) unlink($path);
    }
    $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: patients.php?deleted=1');
exit;
