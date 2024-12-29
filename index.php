
<?php
// index.php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

define('BASE_PATH', __DIR__ . '/../');

require_once  './src/db.php';
require_once './src/functions.php';

session_start();

$url = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI'], '/') : '';

if ($url === '' || $url === '/' || $url == 'index.php' || $url == 'index' || $url == 'home') {
    // Afficher la page d'accueil
    require 'home.php';
    exit();
} else {
    // Chercher le short_code dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM links WHERE short_code = :short_code");
    $stmt->execute(['short_code' => $url]);
    $link = $stmt->fetch();

    if ($link) {
        // Vérifier l'expiration
        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
            // Lien expiré
            header("HTTP/1.0 404 Not Found");
            echo "Lien expiré.";
            exit();
        }

        // Incrémenter le compteur de clics
        $stmt = $pdo->prepare("UPDATE links SET clicks = clicks + 1 WHERE id = :id");
        $stmt->execute(['id' => $link['id']]);

        // Rediriger vers l'URL originale
        header("Location: " . $link['original_url'], true, 301);
        exit();
    } else {
        // Lien non trouvé
        header("HTTP/1.0 404 Not Found");
        echo "Lien non trouvé.";
        exit();
    }
}
?>
