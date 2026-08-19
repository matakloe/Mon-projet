<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$page_title = 'Départements';
$active_page = 'departments';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name === '') {
        $errors[] = 'Le nom du département est obligatoire.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        header('Location: departments.php?created=1');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: departments.php?deleted=1');
    exit;
}

$departments = $pdo->query("
    SELECT dep.*, COUNT(doc.id) AS doctor_count
    FROM departments dep LEFT JOIN doctors doc ON doc.department_id = dep.id
    GROUP BY dep.id ORDER BY dep.name
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div><h1>Départements</h1><div class="subtitle"><?= count($departments) ?> département(s)</div></div>
</div>

<?php if (!empty($_GET['created'])): ?><div class="alert alert-success">Le département a été créé.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success">Le département a été supprimé.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?= h($e) ?></div><?php endforeach; ?>

<div style="display:grid; grid-template-columns: 1fr 1.6fr; gap:20px; align-items:start;">
    <div class="card">
        <h3 style="margin-bottom:14px; font-size:16px;">Nouveau département</h3>
        <form method="post" action="departments.php">
            <input type="hidden" name="action" value="add">
            <div class="field"><label>Nom</label><input type="text" name="name" required></div>
            <div class="field"><label>Description</label><textarea name="description"></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Créer</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom:14px; font-size:16px;">Liste des départements</h3>
        <?php if (!$departments): ?>
            <p style="font-size:13.5px; color:var(--text-muted);">Aucun département enregistré.</p>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Nom</th><th>Description</th><th>Médecins</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($departments as $dep): ?>
                <tr>
                    <td style="font-weight:500;"><?= h($dep['name']) ?></td>
                    <td style="color:var(--text-muted);"><?= h($dep['description'] ?: '—') ?></td>
                    <td><?= (int)$dep['doctor_count'] ?></td>
                    <td style="text-align:right;">
                        <a href="departments.php?delete=<?= (int)$dep['id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Supprimer ce département ?');">Supprimer</a>
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
