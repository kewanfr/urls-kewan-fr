<?php
// public/home.php

require_once './src/db.php';
require_once './src/functions.php';

// session_start();

$public_links = getPublicLinks($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Raccourcisseur d'URLs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

    <body class="bg-gray-100 min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="container mx-auto px-4 py-6">
                <h1 class="text-3xl font-bold text-center text-blue-600">URLs Kéwan.fr</h1>
            </div>
        </header>
    
        <main class="flex-grow container mx-auto px-4 py-8">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="bg-white shadow-md rounded-lg p-6 mb-6 text-center">
                    <p class="text-lg">Bienvenue, <?= htmlspecialchars($_SESSION['user_email']) ?>!</p>
                    <div class="mt-4">
                        <a href="dashboard" class="text-blue-500 hover:text-blue-700 mx-2">Tableau de bord</a>
                        <span class="text-gray-400">|</span>
                        <a href="logout" class="text-blue-500 hover:text-blue-700 mx-2">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white shadow-md rounded-lg p-6 mb-6 text-center">
                    <a href="login" class="text-blue-500 hover:text-blue-700 mx-2">Connexion</a>
                    <span class="text-gray-400">|</span>
                    <a href="register" class="text-blue-500 hover:text-blue-700 mx-2">Inscription</a>
                </div>
            <?php endif; ?>
    
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="create_link" method="POST" class="space-y-6">
                    <div>
                        <label for="original_url" class="block text-sm font-medium text-gray-700">URL à raccourcir :</label>
                        <input type="url" id="original_url" name="original_url" required placeholder="https://exemple.com"
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div>
                            <label for="custom_slug" class="block text-sm font-medium text-gray-700">Slug personnalisé (facultatif) :</label>
                            <input type="text" id="custom_slug" name="custom_slug" pattern="[a-zA-Z0-9_-]{3,30}"
                                   title="3 à 30 caractères alphanumériques, -, _"
                                   placeholder="exemple-slug"
                                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
    
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700">Expiration :</label>
                            <input type="datetime-local" id="expires_at" name="expires_at"
                                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
    
                        <div class="flex items-center">
                            <input type="checkbox" id="is_public" name="is_public" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_public" class="ml-2 block text-sm text-gray-700">Rendre public</label>
                        </div>
                    <?php endif; ?>
    
                    <div class="text-center">
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Raccourcir
                        </button>
                    </div>
                </form>
    
                <?php if (isset($_SESSION['short_url'])): ?>
                    <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <p>URL raccourcie : <a href="<?= htmlspecialchars($_SESSION['short_url']) ?>" class="text-blue-600 underline"><?= htmlspecialchars($_SESSION['short_url']) ?></a></p>
                    </div>
                    <?php unset($_SESSION['short_url']); ?>
                <?php endif; ?>
            </div>
    
            <div class="mt-10">
                <h2 class="text-2xl font-semibold mb-4 text-center text-gray-800">Liens publics</h2>
                <?php if (!empty($public_links)): ?>
                    <ul class="space-y-3">
                        <?php foreach ($public_links as $link): ?>
                            <li class="bg-white shadow p-4 rounded-lg flex justify-between items-center">
                                <div>
                                    <a href="<?= htmlspecialchars($link['short_code']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700 font-medium"><?= htmlspecialchars($link['short_code']) ?></a>
                                    <span class="text-gray-600">→</span>
                                    <span class="text-gray-700"><?= htmlspecialchars($link['original_url']) ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($link['short_code']) ?>" target="_blank" class="text-sm text-green-500 hover:underline">Visiter</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center text-gray-600">Aucun lien public disponible.</p>
                <?php endif; ?>
            </div>
        </main>
    
        <footer class="bg-white shadow mt-10">
            <div class="container mx-auto px-4 py-6 text-center text-gray-500">
            <?= date('Y') ?> Urls Kéwan.fr - Développé par <a href="https://kewan.fr" target="_blank" class="text-blue-600 hover:text-blue-500">Kéwan B</a>
            </div>
        </footer>
</body>
</html>
