<?php

declare(strict_types=1);

namespace Core;

final class Paginator
{
    public const PER_PAGE_OPTIONS = [5, 10, 25, 50];

    public static function request(int $default = 5): array
    {
        $perPage = (int) ($_GET['per_page'] ?? $default);

        return [
            'page' => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : $default,
            'search' => trim((string) ($_GET['search'] ?? '')),
        ];
    }

    public static function result(array $rows, int $total, int $page, int $perPage, array $query = []): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => $rows,
            'query' => $query,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
            'first' => $total === 0 ? 0 : $offset + 1,
            'last' => min($offset + $perPage, $total),
        ];
    }

    public static function offset(int $total, int &$page, int $perPage): int
    {
        $page = min(max(1, $page), max(1, (int) ceil($total / $perPage)));
        return ($page - 1) * $perPage;
    }
}
