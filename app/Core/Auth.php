<?php
namespace App\Core;

use PDO;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        $u = $_SESSION['user'] ?? null;
        return is_array($u) ? $u : null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function attempt(string $username, string $password, PDO $pdo): bool
    {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return false;
        // Existing database accounts may store plain-text passwords. New accounts
        // created through the user form use password_hash(), so support both.
        $storedPassword = (string)$user['password'];
        if (!password_verify($password, $storedPassword) && !hash_equals($storedPassword, $password)) return false;
        $_SESSION['user'] = [
            'id' => (int)$user['id_users'],
            'username' => $user['username'],
            'role' => $user['role'],
            'email' => $user['email'] ?? null,
        ];
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();
        $roles = (array)$roles;
        $user = self::user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }
}
