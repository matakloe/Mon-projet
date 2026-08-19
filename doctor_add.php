<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Nouveau médecin';
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
        $photo = handle_photo_upload('photo', 'doctors');
        $stmt = $pdo->prepare("INSERT INTO doctors (first_name, last_name, specialty, department_id, phone, email, photo) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$first_name, $last_name, $specialty, $department_id, $phone, $email, $photo]);
        header('Location: doctors.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Nouveau médecin</h1><div class="subtitle">Ajouter un praticien à l'hôpital</div></div>
    <a href="doctors.php" class="btn btn-ghost">← Retour à la liste</a>
</div>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div class="card" style="max-width:600px;">
<form method="post" action="doctor_add.php" enctype="multipart/form-data">
    <div class="photo-picker">
        <span class="avatar" style="width:78px;height:78px;font-size:26px;background:#5C7FA6">?</span>
        <div>
            <label style="display:block; font-size:11.5px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); font-family:'IBM Plex Mono',monospace; margin-bottom:6px;">Photo du médecin</label>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
        </div>
    </div>

    <div class="form-grid">
        <div class="field"><label>Prénom</label><input type="text" name="first_name" required></div>
        <div class="field"><label>Nom</label><input type="text" name="last_name" required></div>
        <div class="field"><label>Spécialité</label><input type="text" name="specialty" placeholder="Cardiologue"></div>
        <div class="field"><label>Département</label>
            <select name="department_id">
                <option value="">— Aucun —</option>
                <?php foreach ($departments as $dep): ?>
                    <option value="<?= (int)$dep['id'] ?>"><?= h($dep['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Téléphone</label><input type="tel" name="phone" placeholder="+228 90 00 00 00"></div>
        <div class="field"><label>Email</label><input type="email" name="email"></div>
    </div>

    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="doctors.php" class="btn btn-ghost">Annuler</a>
</form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
