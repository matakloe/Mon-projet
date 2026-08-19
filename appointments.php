<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Rendez-vous';
$active_page = 'appointments';
$errors = [];

// Création d'un rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $doctor_id  = (int)($_POST['doctor_id'] ?? 0);
    $date       = $_POST['appointment_date'] ?? '';
    $time       = $_POST['appointment_time'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    if (!$patient_id || !$doctor_id || !$date || !$time) {
        $errors[] = 'Veuillez renseigner le patient, le médecin, la date et l\'heure.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?,?,?,?,?,'planifie')");
        $stmt->execute([$patient_id, $doctor_id, $date, $time, $reason]);
        header('Location: appointments.php?created=1');
        exit;
    }
}

$patients = $pdo->query("SELECT id, first_name, last_name FROM patients ORDER BY last_name")->fetchAll();
$doctors  = $pdo->query("SELECT id, first_name, last_name FROM doctors ORDER BY last_name")->fetchAll();

$appointments = $pdo->query("
    SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, p.photo AS p_photo,
           d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    JOIN doctors d ON d.id = a.doctor_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Rendez-vous</h1><div class="subtitle"><?= count($appointments) ?> rendez-vous enregistré(s)</div></div>
</div>

<?php if (!empty($_GET['created'])): ?><div class="alert alert-success">Le rendez-vous a été planifié.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Le rendez-vous a été annulé et supprimé.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div style="display:grid; grid-template-columns: 1fr 1.4fr; gap:20px; align-items:start;">
    <div class="card">
        <h3 style="margin-bottom:14px; font-size:16px;">Planifier un rendez-vous</h3>
        <?php if (!$patients || !$doctors): ?>
            <p style="font-size:13px; color:var(--text-muted);">Ajoutez d'abord au moins un patient et un médecin.</p>
        <?php else: ?>
        <form method="post" action="appointments.php">
            <div class="field"><label>Patient</label>
                <select name="patient_id" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= h($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Médecin</label>
                <select name="doctor_id" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($doctors as $d): ?>
                        <option value="<?= (int)$d['id'] ?>">Dr <?= h($d['first_name'] . ' ' . $d['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Date</label><input type="date" name="appointment_date" required></div>
            <div class="field"><label>Heure</label><input type="time" name="appointment_time" required></div>
            <div class="field"><label>Motif</label><input type="text" name="reason" placeholder="Consultation, contrôle…"></div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Planifier</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-bottom:14px; font-size:16px;">Tous les rendez-vous</h3>
        <?php if (!$appointments): ?>
            <p style="font-size:13.5px; color:var(--text-muted);">Aucun rendez-vous enregistré.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Heure</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
                <tr>
                    <td>
                        <div class="avatar-row">
                            <?php if ($a['p_photo']): ?>
                                <img class="avatar" style="width:28px;height:28px" src="uploads/patients/<?= h($a['p_photo']) ?>">
                            <?php else: ?>
                                <span class="avatar" style="width:28px;height:28px;font-size:10px;background:<?= avatar_color($a['p_first'].$a['p_last']) ?>"><?= h(initials($a['p_first'], $a['p_last'])) ?></span>
                            <?php endif; ?>
                            <?= h($a['p_first'] . ' ' . $a['p_last']) ?>
                        </div>
                    </td>
                    <td>Dr <?= h($a['d_first'] . ' ' . $a['d_last']) ?></td>
                    <td><?= format_date_fr($a['appointment_date']) ?></td>
                    <td><?= h(substr($a['appointment_time'],0,5)) ?></td>
                    <td><span class="pill pill-<?= h($a['status']) ?>"><?= h(ucfirst($a['status'])) ?></span></td>
                    <td style="text-align:right;">
                        <a href="appointment_delete.php?id=<?= (int)$a['id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Supprimer ce rendez-vous ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
