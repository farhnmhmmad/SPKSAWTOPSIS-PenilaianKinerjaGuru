<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $rows = $this->db->query('SELECT id_users AS id, username, email, role, created_at FROM users ORDER BY username')->fetchAll();
        $this->render('users/index', ['data'=>$rows]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'penilai';
        if (!in_array($role, ['admin','penilai'], true)) { $role = 'penilai'; }
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') { $this->redirect('/users'); }
        $stmt = $this->db->prepare('INSERT INTO users (username,email,role,password) VALUES (:u,:e,:r,:p)');
        $stmt->execute([
            ':u'=>$username,
            ':e'=>$email === '' ? null : $email,
            ':r'=>$role,
            ':p'=>$password,
        ]);
        $this->flash('User berhasil ditambahkan');
        $this->redirect('/users');
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'penilai';
        if (!in_array($role, ['admin','penilai'], true)) { $role = 'penilai'; }
        $password = $_POST['password'] ?? '';
        if (!$id || $username === '') { $this->redirect('/users'); }
        if ($password !== '') {
            $stmt = $this->db->prepare('UPDATE users SET username=:u,email=:e,role=:r,password=:p WHERE id_users=:id');
            $stmt->execute([
                ':id'=>$id,
                ':u'=>$username,
                ':e'=>$email === '' ? null : $email,
                ':r'=>$role,
                ':p'=>$password,
            ]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username=:u,email=:e,role=:r WHERE id_users=:id');
            $stmt->execute([
                ':id'=>$id,
                ':u'=>$username,
                ':e'=>$email === '' ? null : $email,
                ':r'=>$role,
            ]);
        }
        $this->flash('User berhasil diubah');
        $this->redirect('/users');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $this->db->prepare('DELETE FROM users WHERE id_users=:id');
            $stmt->execute([':id'=>$id]);
        }
        $this->flash('User berhasil dihapus');
        $this->redirect('/users');
    }
}
