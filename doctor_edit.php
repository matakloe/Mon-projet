<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();
if (!$doctor) { header('Location: doctors.php'); exit; }

$page_title = 'Modifier Dr ' . $doctor['last_name'];
$active_page = 'doctors';
$errors = [];
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $specialty  = trim($_POST['specialty'] ?? '');
    $department_id = $_POST['department_id'] ?: null;
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($first_name === '' || $last_name === '') {
        $errors[] = 'Le prénom et le nom sont obligatoires.';
    }

    if (!$errors) {
        $photo = $doctor['photo'];
        $new_photo = handle_photo_upload('photo', 'doctors');
        if ($new_photo) {
            if ($photo) {
                $old_path = __DIR__ . '/uploads/doctors/' . $photo;
                if (is_file($old_path)) unlink($old_path);
            }
            $photo = $new_photo;
        }
        $stmt = $pdo->prepare("UPDATE doctors SET first_name=?, last_name=?, specialty=?, department_id=?, phone=?, email=?, photo=? WHERE id=?");
        $stmt->execute([$first_name, $last_name, $specialty, $department_id, $phone, $email, $photo, $id]);
        header('Location: doctors.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Modifier le médecin</h1><div class="subtitle">Dr <?= h($doctor['first_name'] . ' ' . $doctor['last_name']) ?></div></div>
    <a href="doctors.php" class="btn btn-ghost">← Retour</a>
</div>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div class="card" style="max-width:600px;">
<form method="post" action="doctor_edit.php?id=<?= $id ?>" enctype="multipart/form-data">
    <div class="photo-picker">
        <?php if ($doctor['photo']): ?>
            <img class="preview" src="uploads/doctors/<?= h($doctor['photo']) ?>">
        <?php else: ?>
            <span class="avatar" style="width:78px;height:78px;font-size:26px;background:<?= avatar_color($doctor['first_name'].$doctor['last_name']) ?>"><?= h(initials($doctor['first_name'], $doctor['last_name'])) ?></span>
        <?php endif; ?>
        <div>
            <label style="display:block; font-size:11.5px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); font-family:'IBM Plex Mono',monospace; margin-bottom:6px;">Changer la photo</label>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>

    <div class="form-grid">
        <div class="field"><label>Prénom</label><input type="text" name="first_name" required value="<?= h($doctor['first_name']) ?>"></div>
        <div class="field"><label>Nom</label><input type="text" name="last_name" required value="<?= h($doctor['last_name']) ?>"></div>
        <div class="field"><label>Spécialité</label><input type="text" name="specialty" value="<?= h($doctor['specialty']) ?>"></div>
        <div class="field"><label>Département</label>
            <select name="department_id">
                <option value="">— Aucun —</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>" <?= $dep['id']==$doctor['department_id']?'selected':'' ?>><?= h($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Téléphone</label><input type="tel" name="phone" value="<?= h($doctor['phone']) ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= h($doctor['email']) ?>"></div>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="doctors.php" class="btn btn-ghost">Annuler</a>
</form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
