<?php
// app/Controllers/AuthController.php

namespace App\Controllers;

class AuthController
{
    private string $password;
    private string $username;

    public function __construct()
    {
        // In production, use environment variables
        $this->username = env('ADMIN_USERNAME', 'admin');
        $this->password = env('ADMIN_PASSWORD', 'admin123');
    }

    public function showLogin(): void
    {
        if (isLoggedIn()) {
            redirect('/dashboard.php');
        }
        
        $error = flash('error');
        include PUBLIC_PATH . '/views/login.view.php';
    }

    public function processLogin(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Token tidak valid. Silakan coba lagi.');
            redirect('/login.php');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === $this->username && $password === $this->password) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            redirect('/dashboard.php');
        }

        flash('error', 'Username atau password salah.');
        redirect('/login.php');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect('/login.php');
    }
}
