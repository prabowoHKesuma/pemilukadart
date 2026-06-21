<?php

namespace App\Core;

use App\Models\Region;

class RegionScope
{
    public static function isUnrestricted(): bool
    {
        return Auth::isSuperadmin();
    }

    public static function allowedRegionIds(): array
    {
        if (self::isUnrestricted()) {
            return [];
        }

        $regionId = Auth::regionId();

        if (!$regionId) {
            return [];
        }

        return Region::descendantsIncludingSelf($regionId);
    }

    public static function whereSql(string $alias): array
    {
        if (self::isUnrestricted()) {
            return ['', []];
        }

        $allowedRegionIds = self::allowedRegionIds();

        if (empty($allowedRegionIds)) {
            return ['WHERE 1 = 0', []];
        }

        $placeholders = implode(',', array_fill(0, count($allowedRegionIds), '?'));

        return [
            "WHERE {$alias}.region_id IN ({$placeholders})",
            $allowedRegionIds,
        ];
    }

    public static function andSql(string $alias): array
    {
        if (self::isUnrestricted()) {
            return ['', []];
        }

        $allowedRegionIds = self::allowedRegionIds();

        if (empty($allowedRegionIds)) {
            return ['AND 1 = 0', []];
        }

        $placeholders = implode(',', array_fill(0, count($allowedRegionIds), '?'));

        return [
            "AND {$alias}.region_id IN ({$placeholders})",
            $allowedRegionIds,
        ];
    }

    public static function canAccessRegion(?int $regionId): bool
    {
        if (self::isUnrestricted()) {
            return true;
        }

        if (!$regionId) {
            return false;
        }

        return in_array($regionId, self::allowedRegionIds(), true);
    }
}