<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT photo FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if ($doctor) {
    if ($doctor['photo']) {
        $path = __DIR__ . '/uploads/doctors/' . $doctor['photo'];
        if (is_file($path)) unlink($path);
    }
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: doctors.php?deleted=1');
exit;
