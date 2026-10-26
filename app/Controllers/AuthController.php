<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $role = Auth::user()['role'] ?? '';
            if ($role === 'guest') { header('Location: /peringkat'); return; }
            if ($role === 'penilai') { header('Location: /penilaian'); return; }
            header('Location: /dashboard'); return;
        }
        $error = $_GET['e'] ?? null;
        include __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            header('Location: /login?e=Invalid'); return;
        }
        if (Auth::attempt($username, $password, $this->db)) {
            $role = Auth::user()['role'] ?? '';
            if ($role === 'guest') {
                header('Location: /peringkat');
            } elseif ($role === 'penilai') {
                header('Location: /penilaian');
            } else {
                header('Location: /dashboard');
            }
        } else {
            header('Location: /login?e=Gagal');
        }
    }

    public function guest(): void
    {
        $_SESSION['user'] = [
            'id' => 0,
            'username' => 'Guest',
            'role' => 'guest',
            'email' => null,
        ];
        header('Location: /peringkat');
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
    }
}
