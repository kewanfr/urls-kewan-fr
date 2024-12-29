<?php
// src/classes/User.php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($email, $password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users_links (email, password) VALUES (:email, :password)");
        $stmt->execute(['email' => $email, 'password' => $hashed_password]);
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users_links WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Autres méthodes comme update, delete, etc.
}
?>
