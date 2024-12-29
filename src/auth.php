<?php
// src/auth.php

function requireLogin($pdo) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
        header("Location: login");
        exit();
    }

    // Optionnel : Rafraîchir les informations utilisateur
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT email FROM users_links WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_email'] = $user['email'];
        } else {
            // Utilisateur introuvable, déconnecter
            session_unset();
            session_destroy();
            header("Location: login");
            exit();
        }
    }
}
?>
