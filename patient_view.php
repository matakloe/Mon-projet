<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: patients.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a JOIN doctors d ON d.id = a.doctor_id
    WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$id]);
$appointments = $stmt->fetchAll();

$page_title = $patient['first_name'] . ' ' . $patient['last_name'];
$active_page = 'patients';
$age = calc_age($patient['birth_date']);

require __DIR__ . '/includes/header.php';
?>

<a href="patients.php" style="font-size:13px; color:var(--text-muted); display:inline-flex; align-items:center; gap:5px; margin-bottom:14px;">← Retour à la liste</a>

<?php if (!empty($_GET['created'])): ?><div class="alert alert-success">Le dossier a été créé avec succès.</div><?php endif; ?>
<?php if (!empty($_GET['updated'])): ?><div class="alert alert-success">Le dossier a été mis à jour.</div><?php endif; ?>

<div class="card" style="max-width:720px;">
    <div class="detail-head">
        <?php if ($patient['photo']): ?>
            <img class="avatar" style="width:78px;height:78px" src="uploads/patients/<?= h($patient['photo']) ?>">
        <?php else: ?>
            <span class="avatar" style="width:78px;height:78px;font-size:28px;background:<?= avatar_color($patient['first_name'].$patient['last_name']) ?>"><?= h(initials($patient['first_name'], $patient['last_name'])) ?></span>
        <?php endif; ?>
        <div>
            <h2 style="font-size:22px;"><?= h($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
            <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                <span style="font-size:13px; color:var(--text-muted);">
                    <?= $age !== null ? $age . ' ans' : 'Âge inconnu' ?> ·
                    <?= $patient['gender'] === 'H' ? 'Masculin' : ($patient['gender'] === 'F' ? 'Féminin' : 'Autre') ?>
                </span>
                <span class="pill pill-<?= h($patient['status']) ?>"><?= h(ucfirst($patient['status'])) ?></span>
            </div>
        </div>
        <div class="detail-actions">
            <a href="patient_edit.php?id=<?= $id ?>" class="icon-btn" title="Modifier">✎</a>
            <a href="patient_delete.php?id=<?= $id ?>" class="icon-btn" title="Supprimer" style="border-color:rgba(180,83,76,.4)" onclick="return confirm('Supprimer définitivement ce dossier patient ?');">🗑</a>
        </div>
    </div>

    <?php if ($patient['allergies']): ?>
        <div class="alert-warn">⚠️ Allergies : <?= h($patient['allergies']) ?></div>
    <?php endif; ?>

    <div class="detail-grid">
        <div class="detail-row"><div><div class="label">Date de naissance</div><div class="value"><?= format_date_fr($patient['birth_date']) ?></div></div></div>
        <div class="detail-row"><div><div class="label">Groupe sanguin</div><div class="value"><?= h($patient['blood_type'] ?: '—') ?></div></div></div>
        <div class="detail-row"><div><div class="label">Téléphone</div><div class="value"><?= h($patient['phone'] ?: '—') ?></div></div></div>
        <div class="detail-row"><div><div class="label">Email</div><div class="value"><?= h($patient['email'] ?: '—') ?></div></div></div>
        <div class="detail-row"><div><div class="label">Adresse</div><div class="value"><?= h($patient['address'] ?: '—') ?></div></div></div>
        <div class="detail-row"><div><div class="label">Dossier créé le</div><div class="value"><?= format_date_fr($patient['created_at']) ?></div></div></div>
    </div>

    <div style="margin-top:18px;">
        <div class="label" style="margin-bottom:6px;">Notes cliniques</div>
        <div style="background:#FAFCFB; border:1px solid var(--border); border-radius:9px; padding:12px 14px; font-size:14px; min-height:50px;">
            <?= nl2br(h($patient['notes'] ?: 'Aucune note enregistrée.')) ?>
        </div>
    </div>
</div>

<div class="card" style="max-width:720px; margin-top:18px;">
    <h3 style="margin-bottom:12px; font-size:16px;">Historique des rendez-vous</h3>
    <?php if (!$appointments): ?>
        <p style="color:var(--text-muted); font-size:13.5px;">Aucun rendez-vous enregistré pour ce patient.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Médecin</th><th>Date</th><th>Heure</th><th>Motif</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
                <tr>
                    <td>Dr <?= h($a['d_first'] . ' ' . $a['d_last']) ?></td>
                    <td><?= format_date_fr($a['appointment_date']) ?></td>
                    <td><?= h(substr($a['appointment_time'], 0, 5)) ?></td>
                    <td><?= h($a['reason'] ?: '—') ?></td>
                    <td><span class="pill pill-<?= h($a['status']) ?>"><?= h(ucfirst($a['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
