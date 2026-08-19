<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Médecins';
$active_page = 'doctors';

$search = trim($_GET['q'] ?? '');
$sql = "SELECT doc.*, dep.name AS dep_name FROM doctors doc LEFT JOIN departments dep ON dep.id = doc.department_id WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (doc.first_name LIKE ? OR doc.last_name LIKE ? OR doc.specialty LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY doc.last_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Médecins</h1><div class="subtitle"><?= count($doctors) ?> praticien(s)</div></div>
    <a href="doctor_add.php" class="btn btn-accent">+ Nouveau médecin</a>
</div>

<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">La fiche du médecin a été supprimée.</div><?php endif; ?>

<div class="toolbar">
    <form method="get" action="doctors.php">
        <input type="text" name="q" placeholder="Rechercher un médecin…" value="<?= h($search) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Rechercher</button>
    </form>
</div>

<?php if (!$doctors): ?>
    <div class="card empty-state"><h3>Aucun médecin trouvé</h3><p>Ajoutez un praticien pour commencer.</p></div>
<?php else: ?>
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:14px;">
    <?php foreach ($doctors as $d): ?>
        <div class="card">
            <div class="avatar-row" style="margin-bottom:10px;">
                <?php if ($d['photo']): ?>
                    <img class="avatar" style="width:52px;height:52px" src="uploads/doctors/<?= h($d['photo']) ?>">
                <?php else: ?>
                    <span class="avatar" style="width:52px;height:52px;font-size:17px;background:<?= avatar_color($d['first_name'].$d['last_name']) ?>"><?= h(initials($d['first_name'], $d['last_name'])) ?></span>
                <?php endif; ?>
                <div>
                    <div style="font-family:'Fraunces',serif; font-weight:600; font-size:15.5px;">Dr <?= h($d['first_name'] . ' ' . $d['last_name']) ?></div>
                    <div style="color:var(--text-muted); font-size:12.5px;"><?= h($d['specialty'] ?: '—') ?></div>
                </div>
            </div>
            <div style="font-size:13px; color:var(--text-muted); margin-bottom:4px;">🏥 <?= h($d['dep_name'] ?: 'Aucun département') ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">📞 <?= h($d['phone'] ?: '—') ?></div>
            <div style="display:flex; gap:8px;">
                <a href="doctor_edit.php?id=<?= (int)$d['id'] ?>" class="btn btn-ghost btn-sm">Modifier</a>
                <a href="doctor_delete.php?id=<?= (int)$d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce médecin ?');">Supprimer</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
