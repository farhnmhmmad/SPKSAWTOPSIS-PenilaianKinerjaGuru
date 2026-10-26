<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $sessionUser = Auth::user() ?? [];
        $id = (int)($sessionUser['id'] ?? 0);

        $user = $sessionUser;
        if ($id > 0) {
            $stmt = $this->db->prepare('SELECT id_users AS id, username, email, role, created_at FROM users WHERE id_users = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $fresh = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($fresh) {
                $user = array_merge($user, $fresh);
            }
        }

        $title = 'Profil Saya';
        $this->render('profile/index', compact('title', 'user'));
    }

    public function update(): void
    {
        Auth::requireLogin();
        $sessionUser = Auth::user() ?? [];
        $role = $sessionUser['role'] ?? '';
        if ($role === 'guest') {
            $this->redirect('/profile');
        }

        $id = (int)($sessionUser['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$id || $username === '') {
            $this->flash('Username wajib diisi', 'error');
            $this->redirect('/profile');
        }

        if ($password !== '') {
            $stmt = $this->db->prepare('UPDATE users SET username=:u, email=:e, password=:p WHERE id_users=:id');
            $stmt->execute([
                ':u' => $username,
                ':e' => $email === '' ? null : $email,
                ':p' => $password,
                ':id' => $id,
            ]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username=:u, email=:e WHERE id_users=:id');
            $stmt->execute([
                ':u' => $username,
                ':e' => $email === '' ? null : $email,
                ':id' => $id,
            ]);
        }
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email === '' ? null : $email;

        $this->flash('Profil berhasil diperbarui');
        $this->redirect('/profile');
    }
}
