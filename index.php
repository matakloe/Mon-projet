<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Tableau de bord';
$active_page = 'dashboard';

$total_patients   = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$patients_actifs  = $pdo->query("SELECT COUNT(*) FROM patients WHERE status='actif'")->fetchColumn();
$total_doctors    = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$rdv_a_venir      = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= CURDATE() AND status='planifie'")->fetchColumn();

$prochains_rdv = $pdo->query("
    SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, p.photo AS p_photo,
           d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    JOIN doctors d ON d.id = a.doctor_id
    WHERE a.appointment_date >= CURDATE() AND a.status = 'planifie'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 6
")->fetchAll();

$derniers_patients = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 5")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <div class="subtitle">Bonjour <?= h($_SESSION['full_name']) ?>, voici un aperçu de l'hôpital aujourd'hui.</div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stripe" style="background:#28524E"></div>
        <div class="value"><?= (int)$total_patients ?></div><div class="label">Patients enregistrés</div></div>
    <div class="stat-card"><div class="stripe" style="background:#2E7D6B"></div>
        <div class="value"><?= (int)$patients_actifs ?></div><div class="label">Dossiers actifs</div></div>
    <div class="stat-card"><div class="stripe" style="background:#5C7FA6"></div>
        <div class="value"><?= (int)$total_doctors ?></div><div class="label">Médecins</div></div>
    <div class="stat-card"><div class="stripe" style="background:#D89B3C"></div>
        <div class="value"><?= (int)$rdv_a_venir ?></div><div class="label">Rendez-vous à venir</div></div>
</div>

<div style="display:grid; grid-template-columns: 1.3fr 1fr; gap:20px; align-items:start;">
    <div class="card">
        <h3 style="margin-bottom:14px;">Prochains rendez-vous</h3>
        <?php if (!$prochains_rdv): ?>
            <p style="color:var(--text-muted); font-size:13.5px;">Aucun rendez-vous planifié pour le moment.</p>
        <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead><tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Heure</th></tr></thead>
                <tbody>
                <?php foreach ($prochains_rdv as $rdv): ?>
                    <tr>
                        <td>
                            <div class="avatar-row">
                                <?php if ($rdv['p_photo']): ?>
                                    <img class="avatar" style="width:30px;height:30px" src="uploads/patients/<?= h($rdv['p_photo']) ?>">
                                <?php else: ?>
                                    <span class="avatar" style="width:30px;height:30px;font-size:11px;background:<?= avatar_color($rdv['p_first'].$rdv['p_last']) ?>"><?= h(initials($rdv['p_first'], $rdv['p_last'])) ?></span>
                                <?php endif; ?>
                                <?= h($rdv['p_first'] . ' ' . $rdv['p_last']) ?>
                            </div>
                        </td>
                        <td>Dr <?= h($rdv['d_first'] . ' ' . $rdv['d_last']) ?></td>
                        <td><?= format_date_fr($rdv['appointment_date']) ?></td>
                        <td><?= h(substr($rdv['appointment_time'], 0, 5)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-bottom:14px;">Derniers patients ajoutés</h3>
        <?php if (!$derniers_patients): ?>
            <p style="color:var(--text-muted); font-size:13.5px;">Aucun patient enregistré.</p>
        <?php else: ?>
            <?php foreach ($derniers_patients as $p): ?>
                <a href="patient_view.php?id=<?= (int)$p['id'] ?>" class="avatar-row" style="padding:8px 0; border-bottom:1px solid var(--border); text-decoration:none;">
                    <?php if ($p['photo']): ?>
                        <img class="avatar" style="width:36px;height:36px" src="uploads/patients/<?= h($p['photo']) ?>">
                    <?php else: ?>
                        <span class="avatar" style="width:36px;height:36px;font-size:13px;background:<?= avatar_color($p['first_name'].$p['last_name']) ?>"><?= h(initials($p['first_name'], $p['last_name'])) ?></span>
                    <?php endif; ?>
                    <div>
                        <div style="color:var(--text); font-weight:500; font-size:14px;"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></div>
                        <div style="color:var(--text-muted); font-size:12px;">Ajouté le <?= format_date_fr($p['created_at']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
