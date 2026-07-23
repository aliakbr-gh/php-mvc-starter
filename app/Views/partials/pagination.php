<?php $pages = max(1, (int) ceil($result['total'] / $perPage));
$current = min($page, $pages);
$query = ['search' => $search, 'per_page' => $perPage]; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3">
    <span class="text-body-secondary small">Showing
        <?= $result['total'] ? (($current - 1) * $perPage + 1) : 0 ?>–<?= min($current * $perPage, $result['total']) ?>
        of <?= $result['total'] ?></span>
    <nav aria-label="Table pages">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="<?= $current > 1 ? e(url($baseUrl) . '?' . http_build_query($query + ['page' => $current - 1])) : '#' ?>">Previous</a>
            </li>
            <li class="page-item disabled"><span class="page-link">Page <?= $current ?> of <?= $pages ?></span></li>
            <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="<?= $current < $pages ? e(url($baseUrl) . '?' . http_build_query($query + ['page' => $current + 1])) : '#' ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>