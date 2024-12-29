<?php
// src/classes/Link.php

class Link {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createLink($short_code, $original_url, $user_id = null, $is_public = false, $expires_at = null) {
        $stmt = $this->pdo->prepare("INSERT INTO links (short_code, original_url, user_id, is_public, expires_at) VALUES (:short_code, :original_url, :user_id, :is_public, :expires_at)");
        $stmt->execute([
            'short_code'   => $short_code,
            'original_url' => $original_url,
            'user_id'      => $user_id,
            'is_public'    => $is_public,
            'expires_at'   => $expires_at
        ]);
    }

    // Autres méthodes comme getLink, updateLink, deleteLink, etc.
}
?>
