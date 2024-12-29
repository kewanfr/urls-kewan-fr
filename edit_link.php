<?php
// public/edit_link.php

require_once './src/db.php';
require_once './src/functions.php';
require_once './src/auth.php';

session_start();
requireLogin($pdo);

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard");
    exit();
}

$link_id = intval($_GET['id']);

// Récupérer le lien
$stmt = $pdo->prepare("SELECT * FROM links WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $link_id, 'user_id' => $user_id]);
$link = $stmt->fetch();

$stmt2 = $pdo->prepare("SELECT admin FROM users_links WHERE id = :id");
$stmt2->execute(['id' => $user_id]);
$admin = $stmt2->fetch();

if ($admin['admin'] == 1) {
    $stmt = $pdo->prepare("SELECT * FROM links WHERE id = :id");
    $stmt->execute(['id' => $link_id]);
    $link = $stmt->fetch();
}

if (!$link) {
    $_SESSION['error'] = "Lien introuvable ou vous n'avez pas la permission de l'éditer.";
    header("Location: dashboard");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = filter_var($_POST['original_url'], FILTER_VALIDATE_URL);
    if (!$original_url) {
        $_SESSION['error'] = "URL invalide.";
        header("Location: edit?id=" . $link_id);
        exit();
    }

    $custom_slug = isset($_POST['custom_slug']) ? trim($_POST['custom_slug']) : null;
    $is_public = isset($_POST['is_public']) && $_POST['is_public'] === 'on' ? 1 : 0;
    $expires_at = isset($_POST['expires_at']) && !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;

    if ($custom_slug && $custom_slug !== $link['short_code']) {
        // Vérifier la disponibilité du slug personnalisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE short_code = :short_code AND id != :id");
        $stmt->execute(['short_code' => $custom_slug, 'id' => $link_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Le slug personnalisé est déjà pris.";
            header("Location: edit?id=" . $link_id);
            exit();
        }
        $short_code = $custom_slug;
    } else {
        $short_code = $link['short_code'];
    }

    // Mettre à jour le lien dans la base de données
    $stmt = $pdo->prepare("UPDATE links SET original_url = :original_url, short_code = :short_code, is_public = :is_public, expires_at = :expires_at WHERE id = :id");
    $stmt->execute([
        'original_url' => $original_url,
        'short_code'   => $short_code,
        'is_public'    => $is_public,
        'expires_at'   => $expires_at,
        'id'           => $link_id
    ]);

    $_SESSION['success'] = "Lien mis à jour avec succès.";
    header("Location: dashboard");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditer le Lien</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-center text-blue-600">Éditer le Lien</h1>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="edit?id=<?= $link_id ?>" method="POST" class="space-y-6">
                <div>
                    <label for="original_url" class="block text-sm font-medium text-gray-700">URL à raccourcir :</label>
                    <input type="url" id="original_url" name="original_url" required value="<?= htmlspecialchars($link['original_url']) ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="https://exemple.com">
                </div>

                <div>
                    <label for="custom_slug" class="block text-sm font-medium text-gray-700">Slug personnalisé (facultatif) :</label>
                    <input type="text" id="custom_slug" name="custom_slug" pattern="[a-zA-Z0-9_-]{3,30}" title="3 à 30 caractères alphanumériques, -, _"
                           value="<?= htmlspecialchars($link['short_code']) ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="exemple-slug">
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700">Expiration :</label>
                    <input type="datetime-local" id="expires_at" name="expires_at"
                           value="<?= $link['expires_at'] ? date('Y-m-d\TH:i', strtotime($link['expires_at'])) : '' ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_public" name="is_public" <?= $link['is_public'] ? 'checked' : '' ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_public" class="ml-2 block text-sm text-gray-700">Rendre public</label>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Mettre à jour
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                <a href="dashboard" class="font-medium text-blue-600 hover:text-blue-500">Retour au Tableau de Bord</a>
            </p>
        </div>
    </main>

    <footer class="bg-white shadow mt-10">
        <div class="container mx-auto px-4 py-6 text-center text-gray-500">
            &copy; <?= date('Y') ?> Raccourcisseur d'URLs. Tous droits réservés.
        </div>
    </footer>
</body>
</html>
