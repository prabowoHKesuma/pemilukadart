<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;
use PDO;
use Throwable;

class AuditLog
{
    public static function record(string $action, ?string $description = null, ?int $userId = null): void
    {
        try {
            $pdo = Database::connection();

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (
                    user_id,
                    action,
                    description,
                    ip_address,
                    user_agent,
                    created_at
                ) VALUES (
                    :user_id,
                    :action,
                    :description,
                    :ip_address,
                    :user_agent,
                    NOW()
                )
            ");

            $stmt->execute([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => self::ipAddress(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Throwable $e) {
            // Audit log tidak boleh membuat proses utama gagal.
            // Kalau insert log gagal, sistem tetap jalan.
        }
    }

    public static function all(array $filters = []): array
    {
        $pdo = Database::connection();

        $where = [];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(al.action LIKE ? OR al.description LIKE ? OR u.name LIKE ? OR u.username LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';

            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['action'])) {
            $where[] = "al.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $stmt = $pdo->prepare("
            SELECT
                al.*,
                u.name AS user_name,
                u.username AS username,
                u.role AS user_role
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            {$whereSql}
            ORDER BY al.created_at DESC
            LIMIT 500
        ");

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function actions(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query("
            SELECT DISTINCT action
            FROM audit_logs
            ORDER BY action ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function ipAddress(): ?string
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];

                if (str_contains($ip, ',')) {
                    $parts = explode(',', $ip);
                    $ip = trim($parts[0]);
                }

                return substr($ip, 0, 50);
            }
        }

        return null;
    }
}