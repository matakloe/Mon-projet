<?php
// $active_page doit être défini par la page appelante : 'dashboard' | 'patients' | 'doctors' | 'appointments' | 'departments'
$active_page = $active_page ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' — ' : '' ?>Hôpital Central</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><span class="dot"></span> Hôpital Central</div>
        <nav>
            <a href="index.php" class="<?= $active_page === 'dashboard' ? 'active' : '' ?>">🏠 Tableau de bord</a>
            <a href="patients.php" class="<?= $active_page === 'patients' ? 'active' : '' ?>">🧑‍🤝‍🧑 Patients</a>
            <a href="doctors.php" class="<?= $active_page === 'doctors' ? 'active' : '' ?>">🩺 Médecins</a>
            <a href="appointments.php" class="<?= $active_page === 'appointments' ? 'active' : '' ?>">📅 Rendez-vous</a>
            <a href="departments.php" class="<?= $active_page === 'departments' ? 'active' : '' ?>">🏥 Départements</a>
        </nav>
        <div class="user-box">
            <div class="name"><?= h($_SESSION['full_name'] ?? '') ?></div>
            <a href="logout.php" class="logout">Se déconnecter</a>
        </div>
    </aside>
    <main class="main">
