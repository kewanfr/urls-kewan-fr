<?php
// src/functions.php

function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $short_code = '';
    for ($i = 0; $i < $length; $i++) {
        $short_code .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $short_code;
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    return $protocol . $domain;
}

function getPublicLinks($pdo) {
    $stmt = $pdo->prepare("SELECT short_code, original_url FROM links WHERE is_public = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
