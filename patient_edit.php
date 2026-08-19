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

$page_title = 'Modifier ' . $patient['first_name'];
$active_page = 'patients';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender     = $_POST['gender'] ?? 'F';
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');
    $allergies  = trim($_POST['allergies'] ?? '');
    $status     = $_POST['status'] ?? 'actif';
    $notes      = trim($_POST['notes'] ?? '');

    if ($first_name === '' || $last_name === '') {
        $errors[] = 'Le prénom et le nom sont obligatoires.';
    }

    if (!$errors) {
        $photo = $patient['photo'];
        $new_photo = handle_photo_upload('photo', 'patients');
        if ($new_photo) {
            if ($photo) {
                $old_path = __DIR__ . '/uploads/patients/' . $photo;
                if (is_file($old_path)) unlink($old_path);
            }
            $photo = $new_photo;
        }

        $stmt = $pdo->prepare("UPDATE patients SET
            first_name=?, last_name=?, birth_date=?, gender=?, phone=?, email=?, address=?,
            blood_type=?, allergies=?, status=?, notes=?, photo=? WHERE id=?");
        $stmt->execute([
            $first_name, $last_name, $birth_date ?: null, $gender, $phone, $email,
            $address, $blood_type, $allergies, $status, $notes, $photo, $id
        ]);

        header('Location: patient_view.php?id=' . $id . '&updated=1');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Modifier le dossier</h1><div class="subtitle"><?= h($patient['first_name'] . ' ' . $patient['last_name']) ?></div></div>
    <a href="patient_view.php?id=<?= $id ?>" class="btn btn-ghost">← Annuler</a>
</div>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div class="card" style="max-width:680px;">
<form method="post" action="patient_edit.php?id=<?= $id ?>" enctype="multipart/form-data">
    <div class="photo-picker">
        <?php if ($patient['photo']): ?>
            <img class="preview" src="uploads/patients/<?= h($patient['photo']) ?>">
        <?php else: ?>
            <span class="avatar" style="width:78px;height:78px;font-size:26px;background:<?= avatar_color($patient['first_name'].$patient['last_name']) ?>"><?= h(initials($patient['first_name'], $patient['last_name'])) ?></span>
        <?php endif; ?>
        <div>
            <label style="display:block; font-size:11.5px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); font-family:'IBM Plex Mono',monospace; margin-bottom:6px;">Changer la photo</label>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>

    <div class="form-grid">
        <div class="field"><label>Prénom</label><input type="text" name="first_name" required value="<?= h($patient['first_name']) ?>"></div>
        <div class="field"><label>Nom</label><input type="text" name="last_name" required value="<?= h($patient['last_name']) ?>"></div>
        <div class="field"><label>Date de naissance</label><input type="date" name="birth_date" value="<?= h($patient['birth_date']) ?>"></div>
        <div class="field"><label>Genre</label>
            <select name="gender">
                <option value="F" <?= $patient['gender']==='F'?'selected':'' ?>>Féminin</option>
                <option value="H" <?= $patient['gender']==='H'?'selected':'' ?>>Masculin</option>
                <option value="Autre" <?= $patient['gender']==='Autre'?'selected':'' ?>>Autre</option>
            </select>
        </div>
        <div class="field"><label>Téléphone</label><input type="tel" name="phone" value="<?= h($patient['phone']) ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= h($patient['email']) ?>"></div>
        <div class="field"><label>Adresse</label><input type="text" name="address" value="<?= h($patient['address']) ?>"></div>
        <div class="field"><label>Groupe sanguin</label><input type="text" name="blood_type" value="<?= h($patient['blood_type']) ?>"></div>
        <div class="field"><label>Statut du dossier</label>
            <select name="status">
                <option value="actif" <?= $patient['status']==='actif'?'selected':'' ?>>Actif</option>
                <option value="suivi" <?= $patient['status']==='suivi'?'selected':'' ?>>Suivi</option>
                <option value="archive" <?= $patient['status']==='archive'?'selected':'' ?>>Archivé</option>
            </select>
        </div>
    </div>

    <div class="field"><label>Allergies</label><input type="text" name="allergies" value="<?= h($patient['allergies']) ?>"></div>
    <div class="field"><label>Notes cliniques</label><textarea name="notes"><?= h($patient['notes']) ?></textarea></div>

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="patient_view.php?id=<?= $id ?>" class="btn btn-ghost">Annuler</a>
</form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
