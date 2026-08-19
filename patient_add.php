<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Nouveau patient';
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
        $photo = handle_photo_upload('photo', 'patients');

        $stmt = $pdo->prepare("INSERT INTO patients
            (first_name, last_name, birth_date, gender, phone, email, address, blood_type, allergies, status, notes, photo)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $first_name, $last_name, $birth_date ?: null, $gender, $phone, $email,
            $address, $blood_type, $allergies, $status, $notes, $photo
        ]);

        header('Location: patient_view.php?id=' . $pdo->lastInsertId() . '&created=1');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Nouveau patient</h1><div class="subtitle">Créer un nouveau dossier médical</div></div>
    <a href="patients.php" class="btn btn-ghost">← Retour à la liste</a>
</div>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div class="card" style="max-width:680px;">
<form method="post" action="patient_add.php" enctype="multipart/form-data">
    <div class="photo-picker">
        <span class="avatar" style="width:78px;height:78px;font-size:26px;background:#5C7FA6">?</span>
        <div>
            <label style="display:block; font-size:11.5px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); font-family:'IBM Plex Mono',monospace; margin-bottom:6px;">Photo du patient</label>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>

    <div class="form-grid">
        <div class="field"><label>Prénom</label><input type="text" name="first_name" required value="<?= h($_POST['first_name'] ?? '') ?>"></div>
        <div class="field"><label>Nom</label><input type="text" name="last_name" required value="<?= h($_POST['last_name'] ?? '') ?>"></div>
        <div class="field"><label>Date de naissance</label><input type="date" name="birth_date" value="<?= h($_POST['birth_date'] ?? '') ?>"></div>
        <div class="field"><label>Genre</label>
            <select name="gender">
                <option value="F">Féminin</option>
                <option value="H">Masculin</option>
                <option value="Autre">Autre</option>
            </select>
        </div>
        <div class="field"><label>Téléphone</label><input type="tel" name="phone" placeholder="+228 90 00 00 00" value="<?= h($_POST['phone'] ?? '') ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>"></div>
        <div class="field"><label>Adresse</label><input type="text" name="address" value="<?= h($_POST['address'] ?? '') ?>"></div>
        <div class="field"><label>Groupe sanguin</label><input type="text" name="blood_type" placeholder="O+" value="<?= h($_POST['blood_type'] ?? '') ?>"></div>
        <div class="field"><label>Statut du dossier</label>
            <select name="status">
                <option value="actif">Actif</option>
                <option value="suivi">Suivi</option>
                <option value="archive">Archivé</option>
            </select>
        </div>
    </div>

    <div class="field"><label>Allergies</label><input type="text" name="allergies" placeholder="Aucune connue" value="<?= h($_POST['allergies'] ?? '') ?>"></div>
    <div class="field"><label>Notes cliniques</label><textarea name="notes"><?= h($_POST['notes'] ?? '') ?></textarea></div>

    <button type="submit" class="btn btn-primary">Enregistrer le dossier</button>
    <a href="patients.php" class="btn btn-ghost">Annuler</a>
</form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
