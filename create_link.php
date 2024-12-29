<?php
// public/create_link.php

require_once './src/db.php';
require_once './src/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = filter_var($_POST['original_url'], FILTER_VALIDATE_URL);
    if (!$original_url) {
        $_SESSION['error'] = "URL invalide.";
        header("Location: /");
        exit();
    }

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $custom_slug = isset($_POST['custom_slug']) ? trim($_POST['custom_slug']) : null;
    $is_public = isset($_POST['is_public']) && $_POST['is_public'] === 'on' ? 1 : 0;
    $expires_at = isset($_POST['expires_at']) && !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : ($user_id ? null : date('Y-m-d H:i:s', strtotime('+7 days')));

    if ($custom_slug) {
        // Vérifier la disponibilité du slug personnalisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE short_code = :short_code");
        $stmt->execute(['short_code' => $custom_slug]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Le slug personnalisé est déjà pris.";
            header("Location: /");
            exit();
        }
        $short_code = $custom_slug;
    } else {
        // Générer un short_code unique
        do {
            $short_code = generateShortCode(6);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE short_code = :short_code");
            $stmt->execute(['short_code' => $short_code]);
            $exists = $stmt->fetchColumn() > 0;
        } while ($exists);
    }

    // Insérer le lien dans la base de données
    $stmt = $pdo->prepare("INSERT INTO links (short_code, original_url, user_id, is_public, expires_at) VALUES (:short_code, :original_url, :user_id, :is_public, :expires_at)");
    $stmt->execute([
        'short_code'   => $short_code,
        'original_url' => $original_url,
        'user_id'      => $user_id,
        'is_public'    => $is_public,
        'expires_at'   => $expires_at
    ]);

    $short_url = getBaseUrl() . '/' . $short_code;
    $_SESSION['short_url'] = $short_url;

    header("Location: /");
    exit();
} else {
    header("Location: /");
    exit();
}
?>
