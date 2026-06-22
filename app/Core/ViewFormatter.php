<?php

namespace App\Core;

class ViewFormatter
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function dash(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? self::e($value) : '-';
    }

    public static function dateTime(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y H:i', strtotime($value));
    }

    public static function dateTimeSecond(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y H:i:s', strtotime($value));
    }

    public static function statusBadge(string $status): string
    {
        $status = strtolower($status);

        $class = match ($status) {
            'draft' => 'bg-secondary',
            'open' => 'bg-success',
            'closed' => 'bg-warning text-dark',
            'finished' => 'bg-dark',
            'pending' => 'bg-warning text-dark',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'used' => 'bg-secondary',
            'active' => 'bg-success',
            'expired' => 'bg-danger',
            'revoked' => 'bg-dark',
            default => 'bg-secondary',
        };

        return '<span class="badge ' . $class . '">' . self::e(strtoupper($status)) . '</span>';
    }

    public static function regionLevelLabel(?string $level): string
    {
        return match ($level) {
            'kota' => 'Kota / Kabupaten',
            'kecamatan' => 'Kecamatan',
            'kelurahan' => 'Kelurahan / Desa',
            'rw' => 'RW',
            'rt' => 'RT',
            'custom' => 'Custom',
            default => $level ? self::e($level) : '-',
        };
    }

    public static function channelLabel(?string $channel): string
    {
        return match ($channel) {
            'tps' => 'TPS',
            'remote' => 'Remote',
            'both' => 'TPS / Remote',
            default => $channel ? self::e($channel) : '-',
        };
    }

    public static function percent(int|float $value, int|float $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        return round(($value / $total) * 100, 2) . '%';
    }

    public static function dateTimeLocal(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return date('Y-m-d\TH:i', strtotime($value));
    }

    public static function shortText(?string $value, int $limit = 80): string
    {
        if (!$value) {
            return '-';
        }

        return strlen($value) > $limit
            ? substr($value, 0, $limit) . '...'
            : $value;
    }

    public static function actionBadgeClass(?string $action): string
    {
        $action = strtolower((string) $action);

        if (str_contains($action, 'login')) {
            return 'primary';
        }

        if (str_contains($action, 'create') || str_contains($action, 'store')) {
            return 'success';
        }

        if (str_contains($action, 'update') || str_contains($action, 'status')) {
            return 'warning';
        }

        if (str_contains($action, 'delete')) {
            return 'danger';
        }

        if (str_contains($action, 'vote')) {
            return 'dark';
        }

        if (str_contains($action, 'remote')) {
            return 'info';
        }

        return 'secondary';
    }

    public static function nl2brSafe(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return nl2br(self::e($value));
    }

    public static function channelBadgeClass(?string $channel): string
    {
        return match ($channel) {
            'tps' => 'primary',
            'remote' => 'warning',
            'both' => 'info',
            default => 'secondary',
        };
    }
}