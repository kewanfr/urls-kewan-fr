<?php
// public/login.php

require_once './src/db.php';
require_once './src/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $_SESSION['error'] = "Email et mot de passe requis.";
        header("Location: login");
        exit();
    }

    // Récupérer l'utilisateur depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM users_links WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Authentification réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        header("Location: dashboard");
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: login");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-center text-blue-600">Connexion</h1>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white shadow-md rounded-lg p-6">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="login" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="votre.email@exemple.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe :</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Se souvenir de moi
                        </label>
                    </div>

                    <!-- <div class="text-sm">
                        <a href="forgot_password.php" class="font-medium text-blue-600 hover:text-blue-500">
                            Mot de passe oublié ?
                        </a>
                    </div> -->
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Se connecter
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Pas encore inscrit ? <a href="register" class="font-medium text-blue-600 hover:text-blue-500">Inscription</a>
            </p>
        </div>
    </main>

    <footer class="bg-white shadow mt-10">
        <div class="container mx-auto px-4 py-6 text-center text-gray-500">
        <?= date('Y') ?> Urls Kéwan.fr - Développé par <a href="https://kewan.fr" target="_blank" class="text-blue-600 hover:text-blue-500">Kéwan B</a>
        </div>
    </footer>
</body>
</html>
