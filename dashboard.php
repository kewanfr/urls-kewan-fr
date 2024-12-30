<?php
// public/dashboard.php

require_once './src/db.php';
require_once './src/functions.php';
require_once './src/auth.php';

session_start();
requireLogin($pdo);

// Récupérer les liens de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM links WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$links = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT admin FROM users_links WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$admins = $stmt->fetchAll();

if ($admins[0]['admin'] == 1) {
    $stmt = $pdo->prepare("SELECT * FROM links ORDER BY created_at DESC");
    $stmt->execute();
    $allLinks = $stmt->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-blue-600">Tableau de Bord</h1>
            <div class="flex space-x-4">
                <span class="text-gray-700">Bienvenue, <?= htmlspecialchars($_SESSION['user_email']) ?></span>
                <a href="index" class="text-blue-500 hover:text-blue-700">Accueil</a>
                <a href="logout" class="text-blue-500 hover:text-blue-700">Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <!-- Messages de Session -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Formulaire de Création de Lien -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Créer un nouveau lien</h2>
            <form action="create" method="POST" class="space-y-6">
                <div>
                    <label for="original_url" class="block text-sm font-medium text-gray-700">URL à raccourcir :</label>
                    <input type="url" id="original_url" name="original_url" required placeholder="https://exemple.com"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="custom_slug" class="block text-sm font-medium text-gray-700">Slug personnalisé (facultatif) :</label>
                    <input type="text" id="custom_slug" name="custom_slug" pattern="[a-zA-Z0-9_-]{3,30}" title="3 à 30 caractères alphanumériques, -, _"
                           placeholder="exemple-slug"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700">Expiration :</label>
                    <input type="datetime-local" id="expires_at" name="expires_at"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_public" name="is_public"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_public" class="ml-2 block text-sm text-gray-700">Rendre public</label>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Raccourcir
                    </button>
                </div>
            </form>

            <?php if (isset($_SESSION['short_url'])): ?>
                <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <p>URL raccourcie : <a href="<?= htmlspecialchars($_SESSION['short_url']) ?>" class="text-blue-600 underline" target="_blank"><?= htmlspecialchars($_SESSION['short_url']) ?></a></p>
                </div>
                <?php unset($_SESSION['short_url']); ?>
            <?php endif; ?>
        </div>

        <!-- Liste des Liens de l'Utilisateur -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Mes Liens</h2>
            <?php if (!empty($links)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publique</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clics</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($links as $link): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= htmlspecialchars(getBaseUrl() . '/' . $link['short_code']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                            <?= htmlspecialchars(getBaseUrl() . '/' . $link['short_code']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= htmlspecialchars($link['original_url']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                            <?= htmlspecialchars($link['original_url']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $link['is_public'] ? '<span class="text-green-600 font-medium">Oui</span>' : '<span class="text-red-600 font-medium">Non</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $link['expires_at'] ? htmlspecialchars($link['expires_at']) : '<span class="text-gray-500">Jamais</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= htmlspecialchars($link['clicks']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="edit?id=<?= $link['id'] ?>" class="text-yellow-500 hover:text-yellow-700">Éditer</a>
                                            <a href="delete?id=<?= $link['id'] ?>" onclick="return confirm('Supprimer ce lien ?')" class="text-red-500 hover:text-red-700">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">Vous n'avez encore créé aucun lien. Commencez dès maintenant !</p>
            <?php endif; ?>
        </div>

        <?php if ($admins[0]['admin'] == 1): ?>
            <div class="mt-10">
                <h2 class="text-2xl font-semibold mb-4 text-center text-gray-800">Tous les Liens</h2>
                <?php if (!empty($allLinks)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publique</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clics</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($allLinks as $link): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= htmlspecialchars(getBaseUrl() . '/' . $link['short_code']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                            <?= htmlspecialchars(getBaseUrl() . '/' . $link['short_code']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= htmlspecialchars($link['original_url']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                            <?= htmlspecialchars($link['original_url']) ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $link['is_public'] ? '<span class="text-green-600 font-medium">Oui</span>' : '<span class="text-red-600 font-medium">Non</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $link['expires_at'] ? htmlspecialchars($link['expires_at']) : '<span class="text-gray-500">Jamais</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= htmlspecialchars($link['clicks']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="edit?id=<?= $link['id'] ?>" class="text-yellow-500 hover:text-yellow-700">Éditer</a>
                                            <a href="delete?id=<?= $link['id'] ?>" onclick="return confirm('Supprimer ce lien ?')" class="text-red-500 hover:text-red-700">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-600">Aucun lien public disponible.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    

    <footer class="bg-white shadow mt-10">
        <div class="container mx-auto px-4 py-6 text-center text-gray-500">
            <?= date('Y') ?> Urls Kéwan.fr - Développé par <a href="https://kewan.fr" target="_blank" class="text-blue-600 hover:text-blue-500">Kéwan B</a>
        </div>
    </footer>
</body>
</html>
