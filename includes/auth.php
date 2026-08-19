<?php
/**
 * Gestion de session et fonctions utilitaires communes
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Bloque l'accès à la page si l'utilisateur n'est pas connecté */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/** Échappe une chaîne pour un affichage HTML sûr */
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formate une date en français (ex: 12 août 2026) */
function format_date_fr($date) {
    if (empty($date) || $date === '0000-00-00') return '—';
    $ts = strtotime($date);
    if (!$ts) return '—';
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return date('d', $ts) . ' ' . $mois[date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/** Calcule l'âge à partir d'une date de naissance */
function calc_age($birth_date) {
    if (empty($birth_date) || $birth_date === '0000-00-00') return null;
    $birth = new DateTime($birth_date);
    $now = new DateTime();
    return $now->diff($birth)->y;
}

/** Renvoie une couleur stable (parmi une palette) à partir d'une chaîne, pour les avatars sans photo */
function avatar_color($seed) {
    $palette = ['#2E7D6B', '#D89B3C', '#5C7FA6', '#B4534C', '#7A6BA6', '#3D8C8C'];
    $hash = crc32($seed);
    return $palette[$hash % count($palette)];
}

/** Initiales à partir d'un prénom et d'un nom */
function initials($first, $last) {
    $f = mb_substr(trim($first), 0, 1);
    $l = mb_substr(trim($last), 0, 1);
    return mb_strtoupper($f . $l);
}

/**
 * Traite l'upload d'une photo et renvoie le nom de fichier stocké, ou null si aucun fichier valide.
 * $subdir = 'patients' ou 'doctors'
 */
function handle_photo_upload($field, $subdir) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$field]['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        return null; // type de fichier non autorisé
    }
    if ($_FILES[$field]['size'] > 3 * 1024 * 1024) {
        return null; // > 3 Mo
    }

    $ext = $allowed[$mime];
    $filename = uniqid($subdir . '_', true) . '.' . $ext;
    $destination = __DIR__ . '/../uploads/' . $subdir . '/' . $filename;

    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
        return $filename;
    }
    return null;
}
