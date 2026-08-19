<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
$stmt->execute([$id]);

header('Location: appointments.php?deleted=1');
exit;
