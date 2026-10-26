<?php
namespace App\Core;

use PDO;
use SessionHandlerInterface;
use Throwable;

final class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?string $lockName = null;

    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        if ($this->lockName !== null) {
            try {
                $stmt = $this->pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
                $stmt->execute([':lock_name' => $this->lockName]);
            } catch (Throwable) {
                return false;
            } finally {
                $this->lockName = null;
            }
        }

        return true;
    }

    public function read(string $id): string|false
    {
        $this->lockName = 'spk_session_' . substr(hash('sha256', $id), 0, 40);

        try {
            $lock = $this->pdo->prepare('SELECT GET_LOCK(:lock_name, 5)');
            $lock->execute([':lock_name' => $this->lockName]);
            if ((int)$lock->fetchColumn() !== 1) {
                $this->lockName = null;
                return false;
            }

            $stmt = $this->pdo->prepare(
                'SELECT session_data FROM app_sessions WHERE session_id = :id AND expires_at > UTC_TIMESTAMP() LIMIT 1'
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetchColumn();
            return $data === false ? '' : (string)$data;
        } catch (Throwable) {
            $this->close();
            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        $expiresAt = gmdate('Y-m-d H:i:s', time() + max(1, (int)ini_get('session.gc_maxlifetime')));

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO app_sessions (session_id, session_data, expires_at)
                 VALUES (:id, :data, :expires_at)
                 ON DUPLICATE KEY UPDATE session_data = :updated_data, expires_at = :updated_expires_at'
            );
            return $stmt->execute([
                ':id' => $id,
                ':data' => $data,
                ':expires_at' => $expiresAt,
                ':updated_data' => $data,
                ':updated_expires_at' => $expiresAt,
            ]);
        } catch (Throwable) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM app_sessions WHERE session_id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (Throwable) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM app_sessions WHERE expires_at <= UTC_TIMESTAMP()');
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Throwable) {
            return false;
        }
    }
}
