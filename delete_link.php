<?php
// public/delete_link.php

require_once './src/db.php';
require_once './src/auth.php';

session_start();
requireLogin($pdo);

if (isset($_GET['id'])) {
    $link_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Vérifier que le lien appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM links WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $link_id, 'user_id' => $user_id]);
    $link = $stmt->fetch();

    if ($link) {
        // Supprimer le lien
        $stmt = $pdo->prepare("DELETE FROM links WHERE id = :id");
        $stmt->execute(['id' => $link_id]);

        $_SESSION['success'] = "Lien supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Lien introuvable ou vous n'avez pas la permission de le supprimer.";
    }
}

header("Location: dashboard");
exit();
?>
