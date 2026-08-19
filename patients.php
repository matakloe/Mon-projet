<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Patients';
$active_page = 'patients';

$search = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? 'tous';

$sql = "SELECT * FROM patients WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (in_array($status_filter, ['actif', 'suivi', 'archive'], true)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}
$sql .= " ORDER BY last_name ASC, first_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM patients GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($counts);

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Patients</h1>
        <div class="subtitle"><?= (int)$total ?> dossier<?= $total > 1 ? 's' : '' ?> au total</div>
    </div>
    <a href="patient_add.php" class="btn btn-accent">+ Nouveau patient</a>
</div>

<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-success">Le dossier patient a été supprimé.</div>
<?php endif; ?>

<div class="toolbar">
    <form method="get" action="patients.php">
        <input type="text" name="q" placeholder="Rechercher un patient…" value="<?= h($search) ?>">
        <input type="hidden" name="status" value="<?= h($status_filter) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Rechercher</button>
    </form>
    <div class="filter-tabs">
        <a href="?status=tous&q=<?= urlencode($search) ?>" class="<?= $status_filter === 'tous' ? 'active' : '' ?>">Tous · <?= (int)$total ?></a>
        <a href="?status=actif&q=<?= urlencode($search) ?>" class="<?= $status_filter === 'actif' ? 'active' : '' ?>">Actifs · <?= (int)($counts['actif'] ?? 0) ?></a>
        <a href="?status=suivi&q=<?= urlencode($search) ?>" class="<?= $status_filter === 'suivi' ? 'active' : '' ?>">Suivi · <?= (int)($counts['suivi'] ?? 0) ?></a>
        <a href="?status=archive&q=<?= urlencode($search) ?>" class="<?= $status_filter === 'archive' ? 'active' : '' ?>">Archivés · <?= (int)($counts['archive'] ?? 0) ?></a>
    </div>
</div>

<?php if (!$patients): ?>
    <div class="card empty-state">
        <h3>Aucun patient trouvé</h3>
        <p>Essayez une autre recherche ou ajoutez un nouveau dossier.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <thead>
        <tr><th>Patient</th><th>Âge</th><th>Téléphone</th><th>Groupe sanguin</th><th>Statut</th><th>Dernière visite</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($patients as $p): $age = calc_age($p['birth_date']); ?>
            <tr>
                <td>
                    <a href="patient_view.php?id=<?= (int)$p['id'] ?>" class="avatar-row" style="text-decoration:none;">
                        <?php if ($p['photo']): ?>
                            <img class="avatar" style="width:38px;height:38px" src="uploads/patients/<?= h($p['photo']) ?>">
                        <?php else: ?>
                            <span class="avatar" style="width:38px;height:38px;font-size:13px;background:<?= avatar_color($p['first_name'].$p['last_name']) ?>"><?= h(initials($p['first_name'], $p['last_name'])) ?></span>
                        <?php endif; ?>
                        <span style="color:var(--text); font-weight:500;"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></span>
                    </a>
                </td>
                <td><?= $age !== null ? $age . ' ans' : '—' ?></td>
                <td><?= h($p['phone'] ?: '—') ?></td>
                <td><?= h($p['blood_type'] ?: '—') ?></td>
                <td><span class="pill pill-<?= h($p['status']) ?>"><?= h(ucfirst($p['status'])) ?></span></td>
                <td><?= format_date_fr($p['created_at']) ?></td>
                <td style="text-align:right; white-space:nowrap;">
                    <a href="patient_view.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm">Voir</a>
                    <a href="patient_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm">Modifier</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
