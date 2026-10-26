<?php
namespace App\Core;

class Controller
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = $GLOBALS['pdo'];
    }

    protected function render(string $view, array $data = []): void
    {
        $flashMessages = $this->consumeFlash();
        extract($data, EXTR_SKIP);
        include __DIR__ . '/../Views/' . $view . '.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function back(string $fallback = '/'): void
    {
        $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirect($target);
    }

    protected function flash(string $message, string $type = 'success'): void
    {
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['msg' => $message, 'type' => $type];
    }

    private function consumeFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($flash) ? $flash : [];
    }
}
